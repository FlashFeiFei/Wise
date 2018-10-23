<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 11:55
 */

namespace Wise\Kernel;

use Wise\Kernel\Contracts\MessageInterface;
use Wise\Kernel\Exceptions\BadRequestException;
use Wise\Kernel\Exceptions\InvalidArgumentException;
use Wise\Kernel\Exceptions\RuntimeException;
use Wise\Kernel\Messages\Message;
use Wise\Kernel\Messages\News;
use Wise\Kernel\Messages\NewsItem;
use Wise\Kernel\Messages\Raw as RawMessage;
use Wise\Kernel\Messages\Text;
use Wise\Kernel\Support\XML;
use Wise\Kernel\Traits\Observable;
use Wise\Kernel\Traits\ResponseCastable;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ServerGuard.
 *
 * 1. url 里的 signature 只是将 token+nonce+timestamp 得到的签名，只是用于验证当前请求的，在公众号环境下一直有
 * 2. 企业号消息发送时是没有的，因为固定为完全模式，所以 url 里不会存在 signature, 只有 msg_signature 用于解密消息的
 *
 * @author overtrue <i@overtrue.me>
 */
class ServerGuard
{
    use Observable, ResponseCastable;

    /**
     * @var bool
     */
    protected $alwaysValidate = false;

    /**
     * Empty string.
     */
    const SUCCESS_EMPTY_RESPONSE = 'success';

    /**
     * 消息类型
     * @var array
     */
    const MESSAGE_TYPE_MAPPING = [
        'ticket' => Message::TICK,
    ];

    /**
     * @var \Wise\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     *
     * @param \Wise\Kernel\ServiceContainer $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;

        foreach ($this->app->extension->observers() as $observer) {
            call_user_func_array([$this, 'push'], $observer);
        }
    }

    /**
     * Handle and return response.
     *
     * @return Response
     *
     * @throws BadRequestException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function serve(): Response
    {
        $this->app['logger']->debug('Request received:', [
            'method' => $this->app['request']->getMethod(),
            'uri' => $this->app['request']->getUri(),
            'content-type' => $this->app['request']->getContentType(),
            'content' => $this->app['request']->getContent(),
        ]);

//        $response = $this->validate()->resolve();
        //不知道为什么，直觉就是觉得不需要经过validate
        $response = $this->resolve();

        $this->app['logger']->debug('Server response created:', ['content' => $response->getContent()]);

        return $response;
    }

    /**
     * @return $this
     *
     * @throws \Wise\Kernel\Exceptions\BadRequestException
     */
    public function validate()
    {
        if (!$this->alwaysValidate && !$this->isSafeMode()) {
            return $this;
        }

        if ($this->app['request']->get('signature') !== $this->signature([
                $this->getToken(),
                $this->app['request']->get('timestamp'),
                $this->app['request']->get('nonce'),
            ])) {
            throw new BadRequestException('Invalid request signature.', 400);
        }

        return $this;
    }

    /**
     * Force validate request.
     *
     * @return $this
     */
    public function forceValidate()
    {
        $this->alwaysValidate = true;

        return $this;
    }

    /**
     * Get request message.
     *
     * @return array|\Wise\Kernel\Support\Collection|object|string
     *
     * @throws BadRequestException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    public function getMessage()
    {
        //getContent(boole), boole如果是true返回一个资源
        $message = $this->parseMessage($this->app['request']->getContent(false));

        if (!is_array($message) || empty($message)) {
            throw new BadRequestException('No message received.');
        }

        if ($this->isSafeMode() && !empty($message['Encrypt'])) {
            //智能小程序，目前这一步是必须到达的
            //需要对消息进行解码
            $message = $this->app['encryptor']->decrypt(
                $message['Encrypt'],
                $message['MsgSignature'],
                $message['Nonce'],
                $message['TimeStamp']
            );

            // Handle JSON format.
            $dataSet = json_decode($message, true);

            if ($dataSet && (JSON_ERROR_NONE === json_last_error())) {
                //可以json解析就直接返回了
                return $dataSet;
            }
            //不是json数据就把它转成xml
            $message = XML::parse($message);
        }

        return $this->detectAndCastResponseToType($message, $this->app->config->get('response_type'));
    }

    /**
     * Resolve server request and return the response.
     * 解析三方推送通知事件，并响应
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Wise\Kernel\Exceptions\BadRequestException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    protected function resolve(): Response
    {
        $result = $this->handleRequest();

        if ($this->shouldReturnRawResponse()) {
            return new Response($result['response']);
        }

        return new Response(
            $this->buildResponse($result['to'], $result['from'], $result['response']),
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    /**
     * @return string|null
     */
    protected function getToken()
    {
        return $this->app['config']['token'];
    }

    /**
     * @param string $to
     * @param string $from
     * @param \Wise\Kernel\Contracts\MessageInterface|string|int $message
     *
     * @return string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function buildResponse(string $to, string $from, $message)
    {
        if (empty($message) || self::SUCCESS_EMPTY_RESPONSE === $message) {
            return self::SUCCESS_EMPTY_RESPONSE;
        }

        if ($message instanceof RawMessage) {
            return $message->get('content', self::SUCCESS_EMPTY_RESPONSE);
        }

        if (is_string($message) || is_numeric($message)) {
            $message = new Text((string)$message);
        }

        if (is_array($message) && reset($message) instanceof NewsItem) {
            $message = new News($message);
        }

        if (!($message instanceof Message)) {
            throw new InvalidArgumentException(sprintf('Invalid Messages type "%s".', gettype($message)));
        }

        return $this->buildReply($to, $from, $message);
    }

    /**
     * Handle request.
     *
     * @return array
     *
     * @throws \Wise\Kernel\Exceptions\BadRequestException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     */
    protected function handleRequest(): array
    {
        $castedMessage = $this->getMessage();

        $messageArray = $this->detectAndCastResponseToType($castedMessage, 'array');

        $response = $this->dispatch(self::MESSAGE_TYPE_MAPPING[$messageArray['MsgType'] ?? $messageArray['msg_type'] ?? 'ticket'], $castedMessage);

        return [
            'to' => $messageArray['FromUserName'] ?? '',
            'from' => $messageArray['ToUserName'] ?? '',
            'response' => $response,
        ];
    }

    /**
     * Build reply XML.
     *
     * @param string $to
     * @param string $from
     * @param \Wise\Kernel\Contracts\MessageInterface $message
     *
     * @return string
     */
    protected function buildReply(string $to, string $from, MessageInterface $message): string
    {
        $prepends = [
            'ToUserName' => $to,
            'FromUserName' => $from,
            'CreateTime' => time(),
            'MsgType' => $message->getType(),
        ];

        $response = $message->transformToXml($prepends);

        if ($this->isSafeMode()) {
            $this->app['logger']->debug('Messages safe mode is enabled.');
            $response = $this->app['encryptor']->encrypt($response);
        }

        return $response;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function signature(array $params)
    {
        sort($params, SORT_STRING);

        return sha1(implode($params));
    }

    /**
     * Parse message array from raw php input.
     *
     * @param string $content
     *
     * @return array
     *
     * @throws \Wise\Kernel\Exceptions\BadRequestException
     */
    protected function parseMessage($content)
    {
        try {
            if (0 === stripos($content, '<')) {
                //如果是xml文档，解析程xml
                $content = XML::parse($content);
            } else {
                // Handle JSON format.
                //解析程json
                $dataSet = json_decode($content, true);
                if ($dataSet && (JSON_ERROR_NONE === json_last_error())) {
                    $content = $dataSet;
                }
            }

            return (array)$content;
        } catch (\Exception $e) {
            throw new BadRequestException(sprintf('Invalid message content:(%s) %s', $e->getCode(), $e->getMessage()), $e->getCode());
        }
    }

    /**
     * Check the request message safe mode.
     *
     * @return bool
     */
    protected function isSafeMode(): bool
    {
//        return $this->app['request']->get('signature') && 'aes' === $this->app['request']->get('encrypt_type');
        //不要问我为什么，就是写死，就是返回true，原因是因为它就是安全模式，就是需要解码
        return true;
    }

    /**
     * @return bool
     */
    protected function shouldReturnRawResponse(): bool
    {
        return false;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/19
 * Time: 14:49
 */

namespace Wise\Kernel;

use Wise\Kernel\Contracts\AccessTokenInterface;
use Wise\Kernel\Exceptions\HttpException;
use Wise\Kernel\Exceptions\InvalidArgumentException;
use Wise\Kernel\Traits\HasHttpRequests;
use Wise\Kernel\Traits\InteractsWithCache;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AccessToken.
 *
 * @author overtrue <i@overtrue.me>
 */
abstract class AccessToken implements AccessTokenInterface
{
    use HasHttpRequests, InteractsWithCache;

    /**
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * @var string
     */
    protected $requestMethod = 'GET';

    /**
     * @var string
     */
    protected $endpointToGetToken;

    /**
     * @var string
     */
    protected $queryName;

    /**
     * @var array
     */
    protected $token;

    /**
     * @var int
     */
    protected $safeSeconds = 500;

    /**
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected $cachePrefix = 'Wise.kernel.access_token.';

    /**
     * AccessToken constructor.
     *
     * @param \Pimple\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @return array
     *
     * @throws \Wise\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function getRefreshedToken(): array
    {
        return $this->getToken(true);
    }

    /**
     * @return \Wise\Kernel\Contracts\AccessTokenInterface
     *
     * @throws \Wise\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function refresh(): AccessTokenInterface
    {
        $this->getToken(true);

        return $this;
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *
     * @throws \Wise\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function getToken(bool $refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $token = $this->requestToken($this->getCredentials(), true);

        $this->setToken($token[$this->tokenKey], $token['expires_in'] ?? 3600);

        return $token;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array $requestOptions
     *
     * @return \Psr\Http\Message\RequestInterface
     *
     * @throws \Wise\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        parse_str($request->getUri()->getQuery(), $query);

        $query = http_build_query(array_merge($this->getQuery(), $query));

        return $request->withUri($request->getUri()->withQuery($query));
    }

    /**
     * The request query will be used to add to the request.
     *
     * @return array
     *
     * @throws \Wise\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    protected function getQuery(): array
    {
        return [$this->queryName ?? $this->tokenKey => $this->getToken()[$this->tokenKey]];
    }

    /**
     * @param string $token
     * @param int $lifetime
     *
     * @return \Wise\Kernel\Contracts\AccessTokenInterface
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setToken(string $token, int $lifetime = 3600): AccessTokenInterface
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires_in' => $lifetime,
        ], $lifetime - $this->safeSeconds);

        return $this;
    }

    /**
     * 发送请求获取token需要的数据
     * @param array $credentials
     * @param bool $toArray
     *
     * @return \Psr\Http\Message\ResponseInterface|\Wise\Kernel\Support\Collection|array|object|string
     *
     * @throws \Wise\Kernel\Exceptions\HttpException
     * @throws \Wise\Kernel\Exceptions\InvalidConfigException
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));

        if (empty($result[$this->tokenKey])) {
            throw new HttpException('Request access_token fail: ' . json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }

        return $toArray ? $result : $formatted;
    }

    /**
     * Send http request.
     *
     * @param array $credentials
     *
     * @return ResponseInterface
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    protected function sendRequest(array $credentials): ResponseInterface
    {
        $options = [
            ('GET' === $this->requestMethod) ? 'query' : 'json' => $credentials,
        ];

        return $this->setHttpClient($this->app['http_client'])->request($this->getEndpoint(), $this->requestMethod, $options);
    }

    /**
     * @return string
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function getEndpoint(): string
    {
        if (empty($this->endpointToGetToken)) {
            throw new InvalidArgumentException('No endpoint for access token request.');
        }

        return $this->endpointToGetToken;
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->cachePrefix . md5(json_encode($this->getCredentials()));
    }

    /**
     * Credential for get token.
     *
     * @return array
     */
    abstract protected function getCredentials(): array;

}
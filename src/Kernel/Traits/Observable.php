<?php
/**
 * Created by PhpStorm.
 * User: liangyu
 * Date: 2018/10/22
 * Time: 14:33
 */

namespace Wise\Kernel\Traits;

use Wise\Kernel\Clauses\Clause;
use Wise\Kernel\Contracts\EventHandlerInterface;
use Wise\Kernel\Decorators\FinallyResult;
use Wise\Kernel\Decorators\TerminateResult;
use Wise\Kernel\Exceptions\InvalidArgumentException;
use Wise\Kernel\ServiceContainer;

/**
 * Trait Observable.
 *
 * @author overtrue <i@overtrue.me>
 */
trait Observable
{
    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @var array
     */
    protected $clauses = [];

    /**
     * @param \Closure|EventHandlerInterface|string $handler
     * @param \Closure|EventHandlerInterface|string $condition
     *
     * @return \Wise\Kernel\Clauses\Clause
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function push($handler, $condition = '*')
    {
        list($handler, $condition) = $this->resolveHandlerAndCondition($handler, $condition);

        if (!isset($this->handlers[$condition])) {
            //第一次注册这个事件的时候，初始化一下
            $this->handlers[$condition] = [];
        }
        //一个事件对应N个处理程序
        //注册事件
        array_push($this->handlers[$condition], $handler);

        //返回这个东西有什么用？
        return $this->newClause($handler);
    }

    /**
     * @param \Closure|EventHandlerInterface|string $handler
     * @param \Closure|EventHandlerInterface|string $condition
     *
     * @return \Wise\Kernel\Clauses\Clause
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function unshift($handler, $condition = '*')
    {
        list($handler, $condition) = $this->resolveHandlerAndCondition($handler, $condition);

        if (!isset($this->handlers[$condition])) {
            $this->handlers[$condition] = [];
        }

        array_unshift($this->handlers[$condition], $handler);

        return $this->newClause($handler);
    }

    /**
     * @param string $condition
     * @param \Closure|EventHandlerInterface|string $handler
     *
     * @return \Wise\Kernel\Clauses\Clause
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function observe($condition, $handler)
    {
        return $this->push($handler, $condition);
    }

    /**
     * @param string $condition
     * @param \Closure|EventHandlerInterface|string $handler
     *
     * @return \Wise\Kernel\Clauses\Clause
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     */
    public function on($condition, $handler)
    {
        return $this->push($handler, $condition);
    }

    /**
     * @param string|int $event
     * @param mixed ...$payload
     *
     * @return mixed|null
     */
    public function dispatch($event, $payload)
    {
        return $this->notify($event, $payload);
    }

    /**
     * @param string|int $event
     * @param mixed ...$payload
     *
     * @return mixed|null
     */
    public function notify($event, $payload)
    {
        $result = null;

        foreach ($this->handlers as $condition => $handlers) {
            //一个事件对应N个应用程序的处理
            //通知事件对应的应用程序去处理
            //呵呵，通过EasyWechat大佬的例子，我知道了字符串也能按位与
            if ('*' === $condition || ($condition & $event) === $event) {
                foreach ($handlers as $handler) {
                    if ($clause = $this->clauses[spl_object_hash((object)$handler)] ?? null) {
                        if ($clause->intercepted($payload)) {
                            continue 2;
                        }
                    }
                    $response = $this->callHandler($handler, $payload);

                    switch (true) {
                        case $response instanceof TerminateResult:
                            return $response->content;
                        case true === $response:
                            continue 2;
                        case false === $response:
                            break 2;
                        case !empty($response) && !($result instanceof FinallyResult):
                            $result = $response;
                    }
                }
            }
        }

        return $result instanceof FinallyResult ? $result->content : $result;
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * 创建一个Clause
     * @param mixed $handler
     *
     * @return \Wise\Kernel\Clauses\Clause
     */
    protected function newClause($handler): Clause
    {
        //将匿名函数转化成对象,经过spl_object_hash算出唯一值，存储一个Clause对象
        return $this->clauses[spl_object_hash((object)$handler)] = new Clause();
    }

    /**
     * @param callable $handler
     * @param mixed $payload
     *
     * @return mixed
     */
    protected function callHandler(callable $handler, $payload)
    {
        try {
            return $handler($payload);
        } catch (\Exception $e) {
            if (property_exists($this, 'app') && $this->app instanceof ServiceContainer) {
                $this->app['logger']->error($e->getCode() . ': ' . $e->getMessage(), [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        }
    }

    /**
     * 做一些验证，防止参数传错，代码不按正常套路走
     * @param $handler
     *
     * @return \Closure
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     */
    protected function makeClosure($handler)
    {
        if (is_callable($handler)) {
            //是回调，参数没有传错,按正常套路走，直接返回匿名
            return $handler;
        }

        if (is_string($handler)) {
            //一个包括完成命名空间的类名
            if (!class_exists($handler)) {
                //回调类找不到，抛出异常
                throw new InvalidArgumentException(sprintf('Class "%s" not exists.', $handler));
            }
            //通过ReflectionClass 类的getInterfaceNames获取类中所有的接口方法
            if (!in_array(EventHandlerInterface::class, (new \ReflectionClass($handler))->getInterfaceNames(), true)) {
                //判断类是否实现了EventHandlerInterface接口,
                //路过没有实现EventHandlerInterface接口，程序无法继续往下走，抛出异常
                throw new InvalidArgumentException(sprintf('Class "%s" not an instance of "%s".', $handler, EventHandlerInterface::class));
            }
            //创建一个匿名函数，匿名函数所做的事情就是执行，处理程序
            return function ($payload) use ($handler) {
                //动态的这个对应的类
                return (new $handler($this->app ?? null))->handle($payload);
            };
        }

        //handler不是匿名函数和类名的时候
        //是一个类实例出来的对象,直接执行回调
        if ($handler instanceof EventHandlerInterface) {
            return function () use ($handler) {
                return $handler->handle(...func_get_args());
            };
        }

        throw new InvalidArgumentException('No valid handler is found in arguments.');
    }

    /**
     * 加工监听器，变成统一处理?
     * @param $handler
     * @param $condition
     *
     * @return array
     *
     * @throws \Wise\Kernel\Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     */
    protected function resolveHandlerAndCondition($handler, $condition): array
    {
        if (is_int($handler) || (is_string($handler) && !class_exists($handler))) {
            //这是什么情况 ？  handler是个整形？ 或者 handler的类不存在的时候
            //hanler和条件互换一下位置???
            //这是什么鬼???
            //我目前理解的代码中并没有走到这个里面来
            //难道是为了做参数位置传错处理?
            list($handler, $condition) = [$condition, $handler];
        }
        //makeClosure返回一个匿名函数
        return [$this->makeClosure($handler), $condition];
    }
}
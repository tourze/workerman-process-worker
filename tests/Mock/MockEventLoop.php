<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Mock;

use Workerman\Events\EventInterface;

/**
 * 用于测试的事件循环模拟实现
 */
class MockEventLoop implements EventInterface
{
    /**
     * 存储可读事件回调
     *
     * @var array<string, callable>
     */
    private array $readCallbacks = [];
    
    /**
     * 存储可写事件回调
     *
     * @var array<string, callable>
     */
    private array $writeCallbacks = [];
    
    /**
     * 存储信号事件回调
     *
     * @var array<int, callable>
     */
    private array $signalCallbacks = [];
    
    /**
     * 存储定时器
     *
     * @var array<int, array>
     */
    private array $timers = [];
    
    /**
     * 定时器计数器
     */
    private int $timerCounter = 0;
    
    /**
     * 添加可读事件
     *
     * @param resource $stream 流资源
     * @param callable $func 回调函数
     * @return void
     */
    public function onReadable($stream, callable $func): void
    {
        $key = $this->getStreamKey($stream);
        $this->readCallbacks[$key] = $func;
    }
    
    /**
     * 移除可读事件
     *
     * @param resource $stream 流资源
     * @return bool
     */
    public function offReadable($stream): bool
    {
        $key = $this->getStreamKey($stream);
        if (isset($this->readCallbacks[$key])) {
            unset($this->readCallbacks[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * 添加可写事件
     *
     * @param resource $stream 流资源
     * @param callable $func 回调函数
     * @return void
     */
    public function onWritable($stream, callable $func): void
    {
        $key = $this->getStreamKey($stream);
        $this->writeCallbacks[$key] = $func;
    }
    
    /**
     * 移除可写事件
     *
     * @param resource $stream 流资源
     * @return bool
     */
    public function offWritable($stream): bool
    {
        $key = $this->getStreamKey($stream);
        if (isset($this->writeCallbacks[$key])) {
            unset($this->writeCallbacks[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * 添加信号事件
     *
     * @param int $signal 信号
     * @param callable $func 回调函数
     * @return void
     */
    public function onSignal(int $signal, callable $func): void
    {
        $this->signalCallbacks[$signal] = $func;
    }
    
    /**
     * 移除信号事件
     *
     * @param int $signal 信号
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        if (isset($this->signalCallbacks[$signal])) {
            unset($this->signalCallbacks[$signal]);
            return true;
        }
        return false;
    }
    
    /**
     * 延迟执行回调
     *
     * @param float $delay 延迟时间
     * @param callable $func 回调函数
     * @param array $args 参数
     * @return int 定时器ID
     */
    public function delay(float $delay, callable $func, array $args = []): int
    {
        $timerId = ++$this->timerCounter;
        $this->timers[$timerId] = [
            'type' => 'delay',
            'delay' => $delay,
            'func' => $func,
            'args' => $args,
        ];
        return $timerId;
    }
    
    /**
     * 取消延迟定时器
     *
     * @param int $timerId 定时器ID
     * @return bool
     */
    public function offDelay(int $timerId): bool
    {
        if (isset($this->timers[$timerId]) && $this->timers[$timerId]['type'] === 'delay') {
            unset($this->timers[$timerId]);
            return true;
        }
        return false;
    }
    
    /**
     * 重复执行回调
     *
     * @param float $interval 时间间隔
     * @param callable $func 回调函数
     * @param array $args 参数
     * @return int 定时器ID
     */
    public function repeat(float $interval, callable $func, array $args = []): int
    {
        $timerId = ++$this->timerCounter;
        $this->timers[$timerId] = [
            'type' => 'repeat',
            'interval' => $interval,
            'func' => $func,
            'args' => $args,
        ];
        return $timerId;
    }
    
    /**
     * 取消重复定时器
     *
     * @param int $timerId 定时器ID
     * @return bool
     */
    public function offRepeat(int $timerId): bool
    {
        if (isset($this->timers[$timerId]) && $this->timers[$timerId]['type'] === 'repeat') {
            unset($this->timers[$timerId]);
            return true;
        }
        return false;
    }
    
    /**
     * 停止所有定时器
     *
     * @return void
     */
    public function stop(): void
    {
        $this->timers = [];
    }
    
    /**
     * 删除所有定时器
     *
     * @return void
     */
    public function deleteAllTimer(): void
    {
        $this->timers = [];
    }
    
    /**
     * 运行事件循环
     *
     * @return void
     */
    public function run(): void
    {
        // 模拟事件循环运行，实际上这里不做任何事情
    }
    
    /**
     * 获取定时器数量
     *
     * @return int
     */
    public function getTimerCount(): int
    {
        return count($this->timers);
    }
    
    /**
     * 设置错误处理器
     *
     * @param callable $errorHandler 错误处理器
     * @return void
     */
    public function setErrorHandler(callable $errorHandler): void
    {
        // 记录错误处理器，但在测试中不使用它
    }
    
    /**
     * 获取流的唯一键
     *
     * @param resource $stream 流资源
     * @return string
     */
    private function getStreamKey($stream): string
    {
        if (is_resource($stream)) {
            return (string) $stream;
        }
        
        return spl_object_hash($stream);
    }
    
    /**
     * 模拟触发可读事件
     *
     * @param resource $stream 流资源
     * @return bool 是否成功触发
     */
    public function triggerReadable($stream): bool
    {
        $key = $this->getStreamKey($stream);
        
        if (isset($this->readCallbacks[$key])) {
            call_user_func($this->readCallbacks[$key], $stream);
            return true;
        }
        
        return false;
    }
    
    /**
     * 模拟触发可写事件
     *
     * @param resource $stream 流资源
     * @return bool 是否成功触发
     */
    public function triggerWritable($stream): bool
    {
        $key = $this->getStreamKey($stream);
        
        if (isset($this->writeCallbacks[$key])) {
            call_user_func($this->writeCallbacks[$key], $stream);
            return true;
        }
        
        return false;
    }
    
    /**
     * 模拟触发信号事件
     *
     * @param int $signal 信号
     * @return bool 是否成功触发
     */
    public function triggerSignal(int $signal): bool
    {
        if (isset($this->signalCallbacks[$signal])) {
            call_user_func($this->signalCallbacks[$signal], $signal);
            return true;
        }
        
        return false;
    }
    
    /**
     * 模拟触发定时器
     *
     * @param int $timerId 定时器ID
     * @return bool 是否成功触发
     */
    public function triggerTimer(int $timerId): bool
    {
        if (isset($this->timers[$timerId])) {
            $timer = $this->timers[$timerId];
            call_user_func_array($timer['func'], $timer['args']);
            
            if ($timer['type'] === 'delay') {
                unset($this->timers[$timerId]);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取所有注册的可读事件回调
     *
     * @return array<string, callable>
     */
    public function getReadCallbacks(): array
    {
        return $this->readCallbacks;
    }
    
    /**
     * 获取所有注册的可写事件回调
     *
     * @return array<string, callable>
     */
    public function getWriteCallbacks(): array
    {
        return $this->writeCallbacks;
    }
    
    /**
     * 获取所有注册的信号事件回调
     *
     * @return array<int, callable>
     */
    public function getSignalCallbacks(): array
    {
        return $this->signalCallbacks;
    }
    
    /**
     * 获取所有定时器
     *
     * @return array<int, array>
     */
    public function getTimers(): array
    {
        return $this->timers;
    }
}

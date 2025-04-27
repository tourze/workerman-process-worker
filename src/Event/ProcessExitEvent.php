<?php

namespace Tourze\Workerman\ProcessWorker\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * 进程退出事件
 */
class ProcessExitEvent extends Event
{
    /**
     * 事件名称
     */
    public const NAME = 'process.exit';

    /**
     * @param ProcessWorker $worker 进程工作器
     */
    public function __construct(private readonly ProcessWorker $worker)
    {
    }

    /**
     * 获取进程工作器
     *
     * @return ProcessWorker
     */
    public function getWorker(): ProcessWorker
    {
        return $this->worker;
    }
}

<?php

namespace Tourze\Workerman\ProcessWorker\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * 进程启动事件
 */
class ProcessStartEvent extends Event
{
    /**
     * 事件名称
     */
    public const NAME = 'process.start';

    /**
     * @param ProcessWorker $worker 进程工作器
     */
    public function __construct(private readonly ProcessWorker $worker)
    {
    }

    /**
     * 获取进程工作器
     */
    public function getWorker(): ProcessWorker
    {
        return $this->worker;
    }
}

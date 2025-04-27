<?php

namespace Tourze\Workerman\ProcessWorker\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\Workerman\ProcessWorker\ProcessWorker;

/**
 * 进程输出事件
 */
class ProcessOutputEvent extends Event
{
    /**
     * 事件名称
     */
    public const NAME = 'process.output';

    /**
     * @param ProcessWorker $worker 进程工作器
     * @param string $output 输出内容
     */
    public function __construct(
        private readonly ProcessWorker $worker,
        private readonly string $output,
    )
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

    /**
     * 获取输出内容
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }
}

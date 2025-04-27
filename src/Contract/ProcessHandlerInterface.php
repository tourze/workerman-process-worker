<?php

namespace Tourze\Workerman\ProcessWorker\Contract;

/**
 * 进程处理器接口
 */
interface ProcessHandlerInterface
{
    /**
     * 启动进程
     *
     * @return resource 返回进程资源
     */
    public function start(): mixed;

    /**
     * 停止进程
     *
     * @param resource $process 进程资源
     * @return void
     */
    public function stop(mixed $process): void;

    /**
     * 检查进程是否在运行
     *
     * @param resource $process 进程资源
     * @return bool
     */
    public function isRunning(mixed $process): bool;

    /**
     * 获取运行的命令
     *
     * @return string
     */
    public function getCommand(): string;
}

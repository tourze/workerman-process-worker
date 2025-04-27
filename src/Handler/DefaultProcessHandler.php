<?php

namespace Tourze\Workerman\ProcessWorker\Handler;

use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;

/**
 * 默认进程处理器实现
 */
class DefaultProcessHandler implements ProcessHandlerInterface
{
    /**
     * @param string $command 要执行的命令
     */
    public function __construct(private readonly string $command)
    {
    }

    public function start(): mixed
    {
        // 使用popen打开一个进程
        return popen($this->command, 'r');
    }

    public function stop(mixed $process): void
    {
        if (is_resource($process)) {
            fclose($process);
        }
    }

    public function isRunning(mixed $process): bool
    {
        return is_resource($process) && !feof($process);
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}

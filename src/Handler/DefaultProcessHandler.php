<?php

namespace Tourze\Workerman\ProcessWorker\Handler;

use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;
use Tourze\Workerman\ProcessWorker\Exception\ProcessException;

/**
 * 默认进程处理器实现
 */
readonly class DefaultProcessHandler implements ProcessHandlerInterface
{
    /**
     * @param string $command 要执行的命令
     */
    public function __construct(private string $command)
    {
    }

    public function start(): mixed
    {
        // 使用popen打开一个进程
        $resource = popen($this->command, 'r');
        if (false === $resource) {
            throw new ProcessException('Failed to start process: ' . $this->command);
        }

        return $resource;
    }

    public function stop(mixed $process): void
    {
        if (is_resource($process)) {
            pclose($process);
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

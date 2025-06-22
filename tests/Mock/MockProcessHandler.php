<?php

namespace Tourze\Workerman\ProcessWorker\Tests\Mock;

use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;

/**
 * 用于测试的 ProcessHandler 模拟实现
 */
class MockProcessHandler implements ProcessHandlerInterface
{
    /**
     * 模拟进程状态
     */
    private bool $isActive = true;
    
    /**
     * 模拟进程资源
     */
    private $mockResource;
    
    /**
     * 模拟输出行
     *
     * @var array<string>
     */
    private array $outputLines = [];
    
    /**
     * @param string $command 要执行的命令
     * @param array $outputLines 模拟的输出行
     */
    public function __construct(
        private readonly string $command,
        array $outputLines = []
    ) {
        $this->outputLines = $outputLines;
        $this->mockResource = fopen('php://memory', 'r+');
        
        // 如果有输出行，写入到内存流中
        if (!empty($this->outputLines)) {
            fwrite($this->mockResource, implode(PHP_EOL, $this->outputLines));
            rewind($this->mockResource);
        }
    }
    
    /**
     * 启动进程
     *
     * @return resource 返回进程资源
     */
    public function start(): mixed
    {
        $this->isActive = true;
        return $this->mockResource;
    }
    
    /**
     * 停止进程
     *
     * @param resource $process 进程资源
     * @return void
     */
    public function stop(mixed $process): void
    {
        $this->isActive = false;
    }
    
    /**
     * 检查进程是否在运行
     *
     * @param resource $process 进程资源
     * @return bool
     */
    public function isRunning(mixed $process): bool
    {
        return $this->isActive && $process === $this->mockResource;
    }
    
    /**
     * 获取运行的命令
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }
    
    /**
     * 模拟进程退出
     */
    public function simulateExit(): void
    {
        $this->isActive = false;
    }
    
    /**
     * 获取模拟资源
     *
     * @return resource
     */
    public function getMockResource(): mixed
    {
        return $this->mockResource;
    }
}

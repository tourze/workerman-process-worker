<?php

namespace Tourze\Workerman\ProcessWorker;

use Workerman\Worker;

class ProcessWorker extends Worker
{
    public mixed $onProcessOutput = null;
    public mixed $onProcessExit = null;

    public function __construct(private readonly string $runCommand)
    {
        parent::__construct();
        $this->reloadable = true;
        $this->count = 1;
        $this->onWorkerStart = $this->onWorkerStart(...);
    }

    private function onWorkerStart(): void
    {
        // 使用popen打开一个进程
        $pipe = popen($this->runCommand, 'r'); // 替换为你的shell命令
        // 创建读时间
        Worker::$globalEvent->onReadable($pipe, function($pipe) {
            // 读取进程输出
            $output = fread($pipe, 8192);
            if ($output === false || feof($pipe)) {
                // 关闭连接和进程
                fclose($pipe);
                Worker::$globalEvent->offReadable($pipe);
                $this->onProcessExit && call_user_func_array($this->onProcessExit, []);
            } else {
                // 输出发送给客户端
                //echo '收到：' . $output;
                $this->onProcessOutput && call_user_func_array($this->onProcessOutput, [$output]);
            }
        });
    }
}

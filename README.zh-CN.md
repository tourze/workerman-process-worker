# Workerman 进程工作器

[English](README.md) | [中文](README.zh-CN.md)

[![最新版本](https://img.shields.io/packagist/v/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![构建状态](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![质量分数](https://img.shields.io/scrutinizer/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![代码覆盖率](https://img.shields.io/scrutinizer/coverage/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![总下载量](https://img.shields.io/packagist/dt/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![许可证](https://img.shields.io/github/license/tourze/workerman-process-worker.svg?style=flat-square)](https://github.com/tourze/workerman-process-worker/blob/master/LICENSE)

一个 Workerman 扩展，允许您在 Workerman 环境中运行和监控外部进程。此包提供与外部命令的无缝集成，同时保持 Workerman 的事件驱动架构。

灵感来自 https://github.com/workbunny

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装方法](#安装方法)
- [快速开始](#快速开始)
  - [基本用法](#基本用法)
  - [使用事件监听器（推荐）](#使用事件监听器推荐)
- [详细用法](#详细用法)
  - [构造函数参数](#构造函数参数)
  - [事件类型](#事件类型)
  - [事件处理方法](#事件处理方法)
  - [自定义进程处理器](#自定义进程处理器)
  - [Worker 配置](#worker-配置)
  - [高级示例](#高级示例)
- [API 参考](#api-参考)
  - [ProcessWorker 类](#processworker-类)
  - [事件](#事件)
  - [ProcessHandlerInterface](#processhandlerinterface)
- [使用场景](#使用场景)
- [贡献指南](#贡献指南)
- [许可证](#许可证)

## 功能特性

- **进程管理**：将任何外部命令作为 Workerman 进程运行
- **实时监控**：通过事件驱动架构实时监控进程输出
- **事件系统**：内置 Symfony EventDispatcher 提供灵活的事件处理
- **生命周期管理**：处理进程启动、输出和退出事件
- **自定义处理器**：通过 `ProcessHandlerInterface` 支持自定义进程处理器
- **无缝集成**：轻松集成到现有的 Workerman 应用程序中
- **重载支持**：完全支持 Workerman 的重载功能
- **双事件 API**：同时支持回调和事件监听器两种方式

## 系统要求

- PHP 8.1 或更高版本
- Workerman 5.1 或更高版本
- Symfony EventDispatcher 7.3 或更高版本

## 安装方法

通过 Composer 安装：

```bash
composer require tourze/workerman-process-worker
```

## 快速开始

### 基本用法

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// 创建一个带有运行命令的ProcessWorker
$processWorker = new ProcessWorker('ping -c 3 google.com');

// 处理进程输出
$processWorker->onProcessOutput = function ($worker, $output) {
    echo "进程输出: $output";
};

// 处理进程退出
$processWorker->onProcessExit = function ($worker) {
    echo "进程已退出\n";
};

// 启动Workerman
Worker::runAll();
```

### 使用事件监听器（推荐）

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$processWorker = new ProcessWorker('tail -f /var/log/syslog');

// 添加事件监听器
$processWorker->addListener(ProcessStartEvent::NAME, function (ProcessStartEvent $event) {
    echo "进程已启动: " . $event->getWorker()->getRunCommand() . "\n";
});

$processWorker->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    echo "输出: " . $event->getOutput();
});

$processWorker->addListener(ProcessExitEvent::NAME, function (ProcessExitEvent $event) {
    echo "进程已退出\n";
});

Worker::runAll();
```

## 详细用法

### 构造函数参数

`ProcessWorker` 构造函数接受以下参数：

```php
public function __construct(
    string $runCommand,
    ?ProcessHandlerInterface $processHandler = null,
    ?EventDispatcherInterface $eventDispatcher = null
)
```

- `$runCommand`: 要执行的命令
- `$processHandler`: 可选的自定义进程处理器（默认为 `DefaultProcessHandler`）
- `$eventDispatcher`: 可选的自定义事件调度器（默认为新的 `EventDispatcher`）

### 事件类型

此包提供三种主要事件：

1. **ProcessStartEvent**: 进程启动时触发
2. **ProcessOutputEvent**: 进程输出数据时触发
3. **ProcessExitEvent**: 进程终止时触发

### 事件处理方法

#### 1. 回调属性（传统方式）

```php
$processWorker->onProcessStart = function ($worker) {
    echo "进程已启动\n";
};

$processWorker->onProcessOutput = function ($worker, $output) {
    echo "输出: $output";
};

$processWorker->onProcessExit = function ($worker) {
    echo "进程已退出\n";
};
```

#### 2. 事件监听器（推荐）

```php
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;

// 添加具有不同优先级的多个监听器
$processWorker->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    file_put_contents('process.log', $event->getOutput(), FILE_APPEND);
}, 100); // 高优先级

$processWorker->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    echo "输出: " . $event->getOutput();
}, 0); // 默认优先级
```

### 自定义进程处理器

您可以通过实现 `ProcessHandlerInterface` 来实现自己的进程处理器：

```php
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;

class MyProcessHandler implements ProcessHandlerInterface
{
    public function __construct(private string $command) {}
    
    public function start(): mixed
    {
        // 自定义进程启动逻辑
        return popen($this->command, 'r');
    }
    
    public function stop(mixed $process): void
    {
        // 自定义清理逻辑
        if (is_resource($process)) {
            fclose($process);
        }
    }
    
    public function isRunning(mixed $process): bool
    {
        // 自定义运行状态检查
        return is_resource($process) && !feof($process);
    }
    
    public function getCommand(): string
    {
        return $this->command;
    }
}

// 使用自定义处理器
$processWorker = new ProcessWorker('my_command', new MyProcessHandler('my_command'));
```

### Worker 配置

由于 `ProcessWorker` 继承了 Workerman 的 `Worker` 类，您可以像配置任何其他 Worker 一样配置它：

```php
$processWorker->count = 1; // 启动的进程数量
$processWorker->name = 'MyProcessWorker'; // 给Worker命名
$processWorker->reloadable = true; // 启用重载支持（默认：true）
```

### 高级示例

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// 创建用于监控系统日志的ProcessWorker
$logMonitor = new ProcessWorker('tail -f /var/log/nginx/access.log');
$logMonitor->name = 'LogMonitor';

// 记录进程启动
$logMonitor->addListener(ProcessStartEvent::NAME, function (ProcessStartEvent $event) {
    echo "[" . date('Y-m-d H:i:s') . "] 日志监控已启动\n";
});

// 处理日志条目
$logMonitor->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    $line = trim($event->getOutput());
    if (empty($line)) {
        return;
    }
    
    // 解析日志行并根据需要进行处理
    if (strpos($line, 'ERROR') !== false) {
        // 处理错误日志
        echo "[错误] $line\n";
    } else {
        // 处理常规日志
        echo "[信息] $line\n";
    }
});

// 处理进程退出
$logMonitor->addListener(ProcessExitEvent::NAME, function (ProcessExitEvent $event) {
    echo "[" . date('Y-m-d H:i:s') . "] 日志监控已停止\n";
});

Worker::runAll();
```

## API 参考

### ProcessWorker 类

#### 构造函数
```php
public function __construct(
    string $runCommand,
    ?ProcessHandlerInterface $processHandler = null,
    ?EventDispatcherInterface $eventDispatcher = null
)
```

#### 属性
- `$onProcessStart`: 进程启动事件回调
- `$onProcessOutput`: 进程输出事件回调
- `$onProcessExit`: 进程退出事件回调

#### 方法
- `getProcessHandler(): ProcessHandlerInterface` - 获取进程处理器
- `getEventDispatcher(): EventDispatcherInterface` - 获取事件调度器
- `addListener(string $eventName, callable $listener, int $priority = 0): void` - 添加事件监听器
- `getRunCommand(): string` - 获取正在执行的命令

### 事件

#### ProcessStartEvent
- **事件名称**: `process.start`
- **方法**: `getWorker(): ProcessWorker`

#### ProcessOutputEvent
- **事件名称**: `process.output`
- **方法**: `getWorker(): ProcessWorker`, `getOutput(): string`

#### ProcessExitEvent
- **事件名称**: `process.exit`
- **方法**: `getWorker(): ProcessWorker`

### ProcessHandlerInterface

自定义进程处理器的必需方法：

```php
public function start(): mixed;              // 启动进程
public function stop(mixed $process): void; // 停止进程
public function isRunning(mixed $process): bool; // 检查是否运行中
public function getCommand(): string;       // 获取命令
```

## 使用场景

- **日志监控**: 实时监控应用程序或系统日志
- **后台任务**: 运行带有输出监控的后台进程
- **系统命令**: 使用基于事件的处理执行系统命令
- **进程编排**: 协调多个外部进程
- **数据处理**: 使用外部工具进行流数据处理

## 贡献指南

我们欢迎贡献！请随时提交 Pull Request 或创建 Issue 来改进此软件包。

### 开发环境设置

1. 克隆仓库
2. 安装依赖: `composer install`
3. 运行测试: `./vendor/bin/phpunit`
4. 运行静态分析: `./vendor/bin/phpstan analyse`

## 许可证

MIT 许可证 (MIT)。请查看 [许可证文件](LICENSE) 了解更多信息。
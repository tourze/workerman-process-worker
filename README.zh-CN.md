# Workerman 进程工作器

[English](README.md) | [中文](README.zh-CN.md)

[![最新版本](https://img.shields.io/packagist/v/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![总下载量](https://img.shields.io/packagist/dt/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![许可证](https://img.shields.io/github/license/tourze/workerman-process-worker.svg?style=flat-square)](https://github.com/tourze/workerman-process-worker/blob/master/LICENSE)

一个 Workerman 扩展，允许您在 Workerman 环境中运行和监控外部进程。

## 功能特性

- 将任何外部命令作为 Workerman 进程运行
- 实时监控进程输出
- 处理进程退出事件
- 轻松集成到现有的 Workerman 应用程序中
- 支持重新加载

## 安装方法

```bash
composer require tourze/workerman-process-worker
```

## 快速开始

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// 创建一个带有运行命令的ProcessWorker
$processWorker = new ProcessWorker('ping google.com');

// 处理进程输出
$processWorker->onProcessOutput = function ($output) {
    echo "进程输出: $output";
};

// 处理进程退出
$processWorker->onProcessExit = function () {
    echo "进程已退出\n";
};

// 启动Workerman
Worker::runAll();
```

## 详细用法

### 创建 ProcessWorker

`ProcessWorker` 构造函数接受一个将被执行的命令字符串：

```php
$processWorker = new ProcessWorker('your_command_here');
```

### 事件回调

ProcessWorker 提供了两个主要回调：

1. `onProcessOutput`：当进程输出数据时调用

```php
   $processWorker->onProcessOutput = function ($output) {
       // 处理输出
   };
```

2. `onProcessExit`：当进程终止时调用

```php
   $processWorker->onProcessExit = function () {
       // 处理进程退出
   };
```

### Worker 配置

由于 `ProcessWorker` 继承了 Workerman 的 `Worker` 类，您可以像配置任何其他 Worker 一样配置它：

```php
$processWorker->count = 1; // 启动的进程数量
$processWorker->name = 'MyProcessWorker'; // 给Worker命名
```

## 贡献指南

欢迎提交 Pull Request 或创建 Issue 来改进此软件包。

## 许可证

MIT 许可证 (MIT)。请查看 [许可证文件](LICENSE) 了解更多信息。

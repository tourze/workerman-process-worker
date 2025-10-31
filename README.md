# Workerman Process Worker

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![License](https://img.shields.io/github/license/tourze/workerman-process-worker.svg?style=flat-square)](https://github.com/tourze/workerman-process-worker/blob/master/LICENSE)

A Workerman extension that allows you to run and monitor external processes within the Workerman environment. This package provides seamless integration with external commands while maintaining the event-driven architecture of Workerman.

Inspired by https://github.com/workbunny

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
  - [Basic Usage](#basic-usage)
  - [Using Event Listeners (Recommended)](#using-event-listeners-recommended)
- [Detailed Usage](#detailed-usage)
  - [Constructor Parameters](#constructor-parameters)
  - [Event Types](#event-types)
  - [Event Handling Methods](#event-handling-methods)
  - [Custom Process Handler](#custom-process-handler)
  - [Worker Configuration](#worker-configuration)
  - [Advanced Example](#advanced-example)
- [API Reference](#api-reference)
  - [ProcessWorker Class](#processworker-class)
  - [Events](#events)
  - [ProcessHandlerInterface](#processhandlerinterface)
- [Use Cases](#use-cases)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Process Management**: Run any external command as a Workerman process
- **Real-time Monitoring**: Monitor process output in real-time with event-driven architecture
- **Event System**: Built-in Symfony EventDispatcher for flexible event handling
- **Lifecycle Management**: Handle process start, output, and exit events
- **Custom Handlers**: Support for custom process handlers via `ProcessHandlerInterface`
- **Seamless Integration**: Easy integration with existing Workerman applications
- **Reload Support**: Full support for Workerman's reload functionality
- **Dual Event API**: Both callback and event listener approaches supported

## Requirements

- PHP 8.1 or higher
- Workerman 5.1 or higher
- Symfony EventDispatcher 7.3 or higher

## Installation

Install the package via Composer:

```bash
composer require tourze/workerman-process-worker
```

## Quick Start

### Basic Usage

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// Create a ProcessWorker with a command to run
$processWorker = new ProcessWorker('ping -c 3 google.com');

// Handle process output
$processWorker->onProcessOutput = function ($worker, $output) {
    echo "Process output: $output";
};

// Handle process exit
$processWorker->onProcessExit = function ($worker) {
    echo "Process has exited\n";
};

// Start Workerman
Worker::runAll();
```

### Using Event Listeners (Recommended)

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$processWorker = new ProcessWorker('tail -f /var/log/syslog');

// Add event listeners
$processWorker->addListener(ProcessStartEvent::NAME, function (ProcessStartEvent $event) {
    echo "Process started: " . $event->getWorker()->getRunCommand() . "\n";
});

$processWorker->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    echo "Output: " . $event->getOutput();
});

$processWorker->addListener(ProcessExitEvent::NAME, function (ProcessExitEvent $event) {
    echo "Process exited\n";
});

Worker::runAll();
```

## Detailed Usage

### Constructor Parameters

The `ProcessWorker` constructor accepts the following parameters:

```php
public function __construct(
    string $runCommand,
    ?ProcessHandlerInterface $processHandler = null,
    ?EventDispatcherInterface $eventDispatcher = null
)
```

- `$runCommand`: The command to execute
- `$processHandler`: Optional custom process handler (defaults to `DefaultProcessHandler`)
- `$eventDispatcher`: Optional custom event dispatcher (defaults to new `EventDispatcher`)

### Event Types

The package provides three main events:

1. **ProcessStartEvent**: Triggered when the process starts
2. **ProcessOutputEvent**: Triggered when the process outputs data
3. **ProcessExitEvent**: Triggered when the process terminates

### Event Handling Methods

#### 1. Callback Properties (Legacy)

```php
$processWorker->onProcessStart = function ($worker) {
    echo "Process started\n";
};

$processWorker->onProcessOutput = function ($worker, $output) {
    echo "Output: $output";
};

$processWorker->onProcessExit = function ($worker) {
    echo "Process exited\n";
};
```

#### 2. Event Listeners (Recommended)

```php
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;

// Add multiple listeners with different priorities
$processWorker->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    file_put_contents('process.log', $event->getOutput(), FILE_APPEND);
}, 100); // High priority

$processWorker->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    echo "Output: " . $event->getOutput();
}, 0); // Default priority
```

### Custom Process Handler

You can implement your own process handler by implementing the `ProcessHandlerInterface`:

```php
use Tourze\Workerman\ProcessWorker\Contract\ProcessHandlerInterface;

class MyProcessHandler implements ProcessHandlerInterface
{
    public function __construct(private string $command) {}
    
    public function start(): mixed
    {
        // Custom process starting logic
        return popen($this->command, 'r');
    }
    
    public function stop(mixed $process): void
    {
        // Custom cleanup logic
        if (is_resource($process)) {
            fclose($process);
        }
    }
    
    public function isRunning(mixed $process): bool
    {
        // Custom running check
        return is_resource($process) && !feof($process);
    }
    
    public function getCommand(): string
    {
        return $this->command;
    }
}

// Use custom handler
$processWorker = new ProcessWorker('my_command', new MyProcessHandler('my_command'));
```

### Worker Configuration

Since `ProcessWorker` extends Workerman's `Worker` class, you can configure it like any other Worker:

```php
$processWorker->count = 1; // Number of processes to start
$processWorker->name = 'MyProcessWorker'; // Name for the worker
$processWorker->reloadable = true; // Enable reload support (default: true)
```

### Advanced Example

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Tourze\Workerman\ProcessWorker\Event\ProcessStartEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessOutputEvent;
use Tourze\Workerman\ProcessWorker\Event\ProcessExitEvent;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// Create a ProcessWorker for monitoring system logs
$logMonitor = new ProcessWorker('tail -f /var/log/nginx/access.log');
$logMonitor->name = 'LogMonitor';

// Log process start
$logMonitor->addListener(ProcessStartEvent::NAME, function (ProcessStartEvent $event) {
    echo "[" . date('Y-m-d H:i:s') . "] Log monitoring started\n";
});

// Process log entries
$logMonitor->addListener(ProcessOutputEvent::NAME, function (ProcessOutputEvent $event) {
    $line = trim($event->getOutput());
    if (empty($line)) {
        return;
    }
    
    // Parse log line and handle according to your needs
    if (strpos($line, 'ERROR') !== false) {
        // Handle error logs
        echo "[ERROR] $line\n";
    } else {
        // Handle regular logs
        echo "[INFO] $line\n";
    }
});

// Handle process exit
$logMonitor->addListener(ProcessExitEvent::NAME, function (ProcessExitEvent $event) {
    echo "[" . date('Y-m-d H:i:s') . "] Log monitoring stopped\n";
});

Worker::runAll();
```

## API Reference

### ProcessWorker Class

#### Constructor
```php
public function __construct(
    string $runCommand,
    ?ProcessHandlerInterface $processHandler = null,
    ?EventDispatcherInterface $eventDispatcher = null
)
```

#### Properties
- `$onProcessStart`: Callback for process start event
- `$onProcessOutput`: Callback for process output event  
- `$onProcessExit`: Callback for process exit event

#### Methods
- `getProcessHandler(): ProcessHandlerInterface` - Get the process handler
- `getEventDispatcher(): EventDispatcherInterface` - Get the event dispatcher
- `addListener(string $eventName, callable $listener, int $priority = 0): void` - Add event listener
- `getRunCommand(): string` - Get the command being executed

### Events

#### ProcessStartEvent
- **Event Name**: `process.start`
- **Methods**: `getWorker(): ProcessWorker`

#### ProcessOutputEvent  
- **Event Name**: `process.output`
- **Methods**: `getWorker(): ProcessWorker`, `getOutput(): string`

#### ProcessExitEvent
- **Event Name**: `process.exit`
- **Methods**: `getWorker(): ProcessWorker`

### ProcessHandlerInterface

Required methods for custom process handlers:

```php
public function start(): mixed;              // Start the process
public function stop(mixed $process): void; // Stop the process
public function isRunning(mixed $process): bool; // Check if running
public function getCommand(): string;       // Get the command
```

## Use Cases

- **Log Monitoring**: Monitor application or system logs in real-time
- **Background Tasks**: Run background processes with output monitoring
- **System Commands**: Execute system commands with event-based handling
- **Process Orchestration**: Coordinate multiple external processes
- **Data Processing**: Stream data processing with external tools

## Contributing

We welcome contributions! Please feel free to submit pull requests or create issues to improve this package.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `./vendor/bin/phpunit`
4. Run static analysis: `./vendor/bin/phpstan analyse`

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

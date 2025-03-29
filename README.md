# Process Worker for Workerman

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-process-worker.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-process-worker)
[![License](https://img.shields.io/github/license/tourze/workerman-process-worker.svg?style=flat-square)](https://github.com/tourze/workerman-process-worker/blob/master/LICENSE)

A Workerman extension that allows you to run and monitor external processes within the Workerman environment.

## Features

- Run any external command as a Workerman process
- Monitor process output in real-time
- Handle process exit events
- Easily integrate with existing Workerman applications
- Supports reloading

## Installation

```bash
composer require tourze/workerman-process-worker
```

## Quick Start

```php
<?php

use Tourze\Workerman\ProcessWorker\ProcessWorker;
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// Create a ProcessWorker with a command to run
$processWorker = new ProcessWorker('ping google.com');

// Handle process output
$processWorker->onProcessOutput = function ($output) {
    echo "Process output: $output";
};

// Handle process exit
$processWorker->onProcessExit = function () {
    echo "Process has exited\n";
};

// Start Workerman
Worker::runAll();
```

## Detailed Usage

### Creating a ProcessWorker

The `ProcessWorker` constructor takes a command string that will be executed:

```php
$processWorker = new ProcessWorker('your_command_here');
```

### Event Callbacks

ProcessWorker provides two main callbacks:

1. `onProcessOutput`: Called whenever the process outputs data

   ```php
   $processWorker->onProcessOutput = function ($output) {
       // Handle the output
   };
   ```

2. `onProcessExit`: Called when the process terminates

   ```php
   $processWorker->onProcessExit = function () {
       // Handle process exit
   };
   ```

### Worker Configuration

Since `ProcessWorker` extends Workerman's `Worker` class, you can configure it just like any other Worker:

```php
$processWorker->count = 1; // Number of processes to start
$processWorker->name = 'MyProcessWorker'; // Name for the worker
```

## Contributing

Please feel free to submit pull requests or create issues to improve this package.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

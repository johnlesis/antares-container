<?php

require 'vendor/autoload.php';

use Antares\Container\Container;
use Antares\Container\ContainerException;

class Logger {
    public function log(string $message): void {
        echo $message . "\n";
    }
}

class UserService {
    public function __construct(
        private readonly Logger $logger,
    ) {}

    public function create(): void {
        $this->logger->log('User created');
    }
}

$container = new Container();
$service = $container->make(UserService::class);
$service->create();

$container->singleton(Logger::class, fn() => new Logger());
$a = $container->make(Logger::class);
$b = $container->make(Logger::class);
var_dump($a === $b); // should be true

interface LoggerInterface {}
class FileLogger implements LoggerInterface {}

$container->bind(LoggerInterface::class, FileLogger::class);
$logger = $container->make(LoggerInterface::class);
var_dump($logger instanceof FileLogger); // should be true


class C {
    public function __construct(D $d) {}
}

class D {
    public function __construct(C $c) {}
}

try {
    $container->make(C::class);
} catch (ContainerException $e) {
    echo $e->getMessage() . "\n";
}

interface RepositoryInterface {}

try {
    $container->make(RepositoryInterface::class);
} catch (\Antares\Container\ContainerException $e) {
    echo $e->getMessage() . "\n";
}

// test missing type hint
class NoTypeHint
{
    public function __construct(
        private $something,
    ) {}
}

// test builtin with no default
class PrimitiveNoDefault
{
    public function __construct(
        private string $host,
        private int $port,
    ) {}
}

try {
    $container->make(NoTypeHint::class);
} catch (\Antares\Container\ContainerException $e) {
    echo $e->getMessage() . "\n";
}

try {
    $container->make(PrimitiveNoDefault::class);
} catch (\Antares\Container\ContainerException $e) {
    echo $e->getMessage() . "\n";
}
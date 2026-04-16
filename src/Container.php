<?php declare(strict_types=1);

namespace Antares\Container;

use ReflectionClass;

final class Container
{   
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];
    private array $building = [];

    public function bind(string $interface, string $className): void
    {
        $this->bindings[$interface] = $className;
    }

    public function singleton(string $className, callable $callable): void
    {
        $this->singletons[$className] = $callable;
    }

    public function make(string $className)
    {
        if (isset($this->instances[$className])) {
            return $this->instances[$className];
        }

        if (isset($this->singletons[$className])) {
            $this->instances[$className] = ($this->singletons[$className])($this);
            return $this->instances[$className];
        }
        
        if(isset($this->bindings[$className])) {
            return $this->make($this->bindings[$className]);
        } else {

            if (isset($this->building[$className])) {
                throw new ContainerException("Circular dependency detected: {$className}");
            }
            
            $this->building[$className] = true;
            
            $reflectionClass = new ReflectionClass($className);

            $constructor = $reflectionClass->getConstructor();
            if ($constructor === null) {
                unset($this->building[$className]);
                return new $className;
            }

            $parameters = $constructor->getParameters();
            $args = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                    $args[] = $this->make($type->getName());
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $args[] = $parameter->getDefaultValue();
                }
            }
            unset($this->building[$className]);
            return $reflectionClass->newInstanceArgs($args);
        }
    }
}
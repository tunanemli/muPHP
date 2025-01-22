<?php

namespace Core\Database;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use JsonSerializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    protected array $items = [];
    
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    
    public function all(): array
    {
        return $this->items;
    }
    
    public function first(): mixed
    {
        return !empty($this->items) ? reset($this->items) : null;
    }
    
    public function last(): mixed
    {
        return !empty($this->items) ? end($this->items) : null;
    }
    
    public function filter(callable $callback): static
    {
        return new static(array_filter($this->items, $callback));
    }
    
    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }
    
    public function count(): int
    {
        return count($this->items);
    }
    
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }
    
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }
    
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }
    
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
    
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
    
    public function jsonSerialize(): array
    {
        return $this->items;
    }
} 
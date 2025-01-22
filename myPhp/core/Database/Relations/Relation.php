<?php

namespace Core\Database\Relations;

use Core\Database\{Model, QueryBuilder, Collection};

abstract class Relation
{
    protected QueryBuilder $query;
    protected Model $parent;
    protected string $related;
    protected string $foreignKey;
    protected string $localKey;
    protected static bool $constraints = true;
    
    abstract public function __construct(string $related, Model $parent, string $foreignKey, string $localKey);
    abstract public function getResults(): Model|Collection|null;
    
    protected function getRelatedInstance(): Model
    {
        $class = $this->related;
        return new $class;
    }
    
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->addBaseConstraints();
        }
    }
    
    abstract protected function addBaseConstraints(): void;
} 
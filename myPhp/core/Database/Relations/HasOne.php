<?php

namespace Core\Database\Relations;

use Core\Database\Model;

class HasOne extends Relation
{
    public function __construct(string $related, Model $parent, string $foreignKey, string $localKey)
    {
        $this->related = $related;
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        
        $this->query = $related::query();
        $this->addConstraints();
    }
    
    public function getResults(): ?Model
    {
        return $this->query->where($this->foreignKey, $this->parent->{$this->localKey})->first();
    }
    
    protected function addBaseConstraints(): void
    {
        $this->query->where($this->foreignKey, $this->parent->{$this->localKey});
    }
} 
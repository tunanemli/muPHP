<?php

namespace Core\Database\Relations;

use Core\Database\{Model, Collection};

class HasMany extends Relation
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
    
    public function getResults(): Collection
    {
        $results = $this->query->where($this->foreignKey, $this->parent->{$this->localKey})->get();
        return new Collection($results);
    }
    
    protected function addBaseConstraints(): void
    {
        $this->query->where($this->foreignKey, $this->parent->{$this->localKey});
    }
} 
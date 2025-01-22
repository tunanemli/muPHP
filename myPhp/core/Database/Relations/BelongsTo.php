<?php

namespace Core\Database\Relations;

use Core\Database\{Model, Collection};

class BelongsTo extends Relation
{
    protected Model $child;
    protected string $ownerKey;
    
    public function __construct(string $related, Model $child, string $foreignKey, string $ownerKey)
    {
        $this->related = $related;
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->parent = $child;
        $this->localKey = $ownerKey;
        
        $this->query = $related::query();
        $this->addConstraints();
    }
    
    public function getResults(): ?Model
    {
        return $this->query->where($this->ownerKey, $this->child->{$this->foreignKey})->first();
    }
    
    protected function addBaseConstraints(): void
    {
        $this->query->where($this->ownerKey, $this->child->{$this->foreignKey});
    }
} 
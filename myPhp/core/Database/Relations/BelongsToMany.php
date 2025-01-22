<?php

namespace Core\Database\Relations;

use Core\Database\{Model, QueryBuilder, Collection};

class BelongsToMany extends Relation
{
    protected string $table;
    protected string $foreignPivotKey;
    protected string $relatedPivotKey;
    
    public function __construct(string $related, Model $parent, ?string $table, ?string $foreignPivotKey, ?string $relatedPivotKey)
    {
        $this->related = $related;
        $this->parent = $parent;
        $this->table = $table ?? $this->guessPivotTableName();
        $this->foreignPivotKey = $foreignPivotKey ?? $parent->getKeyName();
        $this->relatedPivotKey = $relatedPivotKey ?? 'id';
        $this->foreignKey = $this->foreignPivotKey;
        $this->localKey = $this->relatedPivotKey;
        
        $this->query = $related::query();
        $this->addConstraints();
    }
    
    public function getResults(): Collection
    {
        $results = $this->query
            ->join($this->table, "{$this->related::getTable()}.id", '=', "{$this->table}.{$this->relatedPivotKey}")
            ->where("{$this->table}.{$this->foreignPivotKey}", $this->parent->id)
            ->get();
            
        return new Collection($results);
    }
    
    public function attach(int|string $id, array $attributes = []): bool
    {
        return (bool) $this->newPivotStatement()->insert(array_merge([
            $this->foreignPivotKey => $this->parent->id,
            $this->relatedPivotKey => $id,
        ], $attributes));
    }
    
    public function detach(int|string|array|null $ids = null): bool
    {
        $query = $this->newPivotStatement();
        
        if ($ids !== null) {
            $query->whereIn($this->relatedPivotKey, (array) $ids);
        }
        
        return (bool) $query->where($this->foreignPivotKey, $this->parent->id)->delete();
    }
    
    protected function newPivotStatement(): QueryBuilder
    {
        return new QueryBuilder($this->table);
    }
    
    protected function addBaseConstraints(): void
    {
        $this->query
            ->join($this->table, "{$this->related::getTable()}.id", '=', "{$this->table}.{$this->relatedPivotKey}")
            ->where("{$this->table}.{$this->foreignPivotKey}", $this->parent->id);
    }
    
    private function guessPivotTableName(): string
    {
        $segments = [
            $this->parent::getTable(),
            $this->related::getTable()
        ];
        sort($segments);
        return implode('_', $segments);
    }
} 
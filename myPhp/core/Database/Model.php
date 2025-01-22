<?php

namespace Core\Database;

use Core\Database\Relations\{HasMany, HasOne, BelongsTo, BelongsToMany};
use Core\Database\Exceptions\ModelNotFoundException;
use DateTime;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected static string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $relations = [];
    protected array $hidden = [];
    protected array $fillable = [];
    protected array $casts = [];
    protected array $dates = ['created_at', 'updated_at'];
    protected bool $timestamps = true;
    
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }
    
    public static function query(): QueryBuilder
    {
        return (new QueryBuilder(static::class))->from(static::getTable());
    }
    
    public static function find(int|string $id): ?static
    {
        return static::query()->find($id);
    }
    
    public static function findOrFail(int|string $id): static
    {
        $result = static::find($id);
        
        if (!$result) {
            throw new ModelNotFoundException(static::class, $id);
        }
        
        return $result;
    }
    
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }
    
    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }
    
    public function save(): bool
    {
        $this->updateTimestamps();
        
        $query = new QueryBuilder(static::class);
        
        if (isset($this->attributes[$this->primaryKey])) {
            return $query->where($this->primaryKey, $this->attributes[$this->primaryKey])->update($this->attributes);
        }
        
        $id = $query->insert($this->attributes);
        $this->attributes[$this->primaryKey] = $id;
        return true;
    }
    
    protected function updateTimestamps(): void
    {
        if (!$this->timestamps) {
            return;
        }
        
        $time = new DateTime;
        
        if (!isset($this->attributes['created_at'])) {
            $this->attributes['created_at'] = $time;
        }
        
        $this->attributes['updated_at'] = $time;
    }
    
    public function jsonSerialize(): array
    {
        $attributes = $this->attributes;
        
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        foreach ($this->dates as $date) {
            if (isset($attributes[$date])) {
                $attributes[$date] = $this->formatDate($attributes[$date]);
            }
        }
        
        return array_merge($attributes, $this->relations);
    }
    
    protected function formatDate($date): string
    {
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d H:i:s');
        }
        
        return $date;
    }
    
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        return new HasMany($related, $this, $foreignKey, $localKey ?? $this->primaryKey);
    }
    
    public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        return new BelongsTo($related, $this, $foreignKey, $ownerKey);
    }
    
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        return new HasOne($related, $this, $foreignKey, $localKey ?? $this->primaryKey);
    }
    
    public function belongsToMany(
        string $related, 
        ?string $table = null, 
        ?string $foreignPivotKey = null, 
        ?string $relatedPivotKey = null
    ): BelongsToMany {
        return new BelongsToMany($related, $this, $table, $foreignPivotKey, $relatedPivotKey);
    }
    
    public function delete(): bool
    {
        return static::query()->where($this->primaryKey, $this->attributes[$this->primaryKey])->delete();
    }
    
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }
    
    protected function setAttribute(string $key, mixed $value): void
    {
        if (isset($this->casts[$key])) {
            $value = $this->castAttribute($key, $value);
        }
        $this->attributes[$key] = $value;
    }
    
    protected function castAttribute(string $key, mixed $value): mixed
    {
        return match($this->casts[$key]) {
            'int', 'integer' => (int) $value,
            'real', 'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array' => json_decode($value, true),
            'json' => json_encode($value),
            'datetime' => new DateTime($value),
            default => $value
        };
    }
    
    public static function getTable(): string
    {
        return static::$table;
    }
    
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
    
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }
    
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }
} 
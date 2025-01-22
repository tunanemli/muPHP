<?php

namespace Core\Database;

use PDO;
use PDOStatement;
use InvalidArgumentException;
use Core\Database\Exceptions\ModelNotFoundException;

class QueryBuilder
{
    protected string $model;
    protected string $table;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $orders = [];
    protected array $groups = [];
    protected array $havings = [];
    protected array $joins = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected Connection $connection;
    protected array $bindings = [];
    
    public function __construct(string $model)
    {
        $this->model = $model;
        $this->connection = Connection::getInstance();
    }
    
    public function select(array|string $columns = ['*']): static
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    
    public function from(string $table): static
    {
        $this->table = $table;
        return $this;
    }
    
    public function where(string|array $column, mixed $operator = null, mixed $value = null): static
    {
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->where($key, '=', $value);
            }
            return $this;
        }
        
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = compact('column', 'operator', 'value');
        return $this;
    }
    
    public function whereIn(string $column, array $values): static
    {
        return $this->where($column, 'in', $values);
    }
    
    public function whereNotIn($column, array $values)
    {
        return $this->where($column, 'not in', $values);
    }
    
    public function whereBetween($column, array $values)
    {
        return $this->where($column, 'between', $values);
    }
    
    public function whereNull($column)
    {
        return $this->where($column, 'is', null);
    }
    
    public function whereNotNull($column)
    {
        return $this->where($column, 'is not', null);
    }
    
    public function orderBy($column, $direction = 'asc')
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }
    
    public function groupBy(...$groups)
    {
        $this->groups = array_merge($this->groups, $groups);
        return $this;
    }
    
    public function having($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->havings[] = compact('column', 'operator', 'value');
        return $this;
    }
    
    public function join(
        string $table, 
        string $first, 
        ?string $operator = null, 
        ?string $second = null, 
        string $type = 'inner'
    ): static {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }
    
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }
    
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }
    
    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }
    
    public function offset($value)
    {
        $this->offset = $value;
        return $this;
    }
    
    public function get(): array
    {
        $stmt = $this->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->model);
    }
    
    public function first(): ?object
    {
        $results = $this->limit(1)->get();
        return !empty($results) ? $results[0] : null;
    }
    
    public function paginate($perPage = 15, $page = 1)
    {
        $total = $this->count();
        
        $this->limit($perPage);
        $this->offset(($page - 1) * $perPage);
        
        return new Paginator($this->get(), $total, $perPage, $page);
    }
    
    public function insert(array $values)
    {
        $sql = "INSERT INTO {$this->table} (" . implode(', ', array_keys($values)) . ") VALUES (" . 
               implode(', ', array_fill(0, count($values), '?')) . ")";
               
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_values($values));
        
        return $this->connection->lastInsertId();
    }
    
    public function update(array $values)
    {
        $sql = "UPDATE {$this->table} SET " . 
               implode(', ', array_map(function($key) {
                   return "{$key} = ?";
               }, array_keys($values)));
               
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute(array_merge(array_values($values), $this->getBindings()));
    }
    
    public function delete()
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($this->getBindings());
    }
    
    public function toSql()
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";
        
        if (!empty($this->joins)) {
            $sql .= ' ' . $this->compileJoins();
        }
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }
        
        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->compileHavings();
        }
        
        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . $this->compileOrders();
        }
        
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
    
    public function count(): int
    {
        $clone = clone $this;
        $clone->columns = ['COUNT(*) as count'];
        
        $result = $clone->get();
        return (int) ($result[0]->count ?? 0);
    }
    
    protected function compileWheres()
    {
        return implode(' AND ', array_map(function($where) {
            if ($where['operator'] === 'in') {
                return "{$where['column']} IN (" . implode(', ', array_fill(0, count($where['value']), '?')) . ")";
            }
            
            if ($where['operator'] === 'between') {
                return "{$where['column']} BETWEEN ? AND ?";
            }
            
            return "{$where['column']} {$where['operator']} ?";
        }, $this->wheres));
    }
    
    protected function getBindings()
    {
        $bindings = [];
        
        foreach ($this->wheres as $where) {
            if (is_array($where['value'])) {
                $bindings = array_merge($bindings, $where['value']);
            } else {
                $bindings[] = $where['value'];
            }
        }
        
        return $bindings;
    }
    
    protected function compileJoins()
    {
        return implode(' ', array_map(function($join) {
            return strtoupper($join['type']) . " JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }, $this->joins));
    }
    
    protected function compileOrders()
    {
        return implode(', ', array_map(function($order) {
            return "{$order['column']} " . strtoupper($order['direction']);
        }, $this->orders));
    }
    
    protected function compileHavings()
    {
        return implode(' AND ', array_map(function($having) {
            return "{$having['column']} {$having['operator']} ?";
        }, $this->havings));
    }
    
    protected function execute(): PDOStatement
    {
        $stmt = $this->connection->prepare($this->toSql());
        $stmt->execute($this->getBindings());
        return $stmt;
    }
    
    public function find(int|string $id): ?Model
    {
        return $this->where($this->getModel()->getKeyName(), $id)->first();
    }
    
    public function findOrFail(int|string $id): Model
    {
        $result = $this->find($id);
        
        if (!$result) {
            throw new ModelNotFoundException($this->model, $id);
        }
        
        return $result;
    }
    
    public function chunk(int $count, callable $callback): bool
    {
        $page = 1;
        
        do {
            $results = $this->forPage($page, $count)->get();
            
            if (count($results) === 0) {
                break;
            }
            
            if ($callback($results, $page) === false) {
                return false;
            }
            
            $page++;
        } while (count($results) === $count);
        
        return true;
    }
    
    public function forPage(int $page, int $perPage): static
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }
    
    protected function getModel(): Model
    {
        return new $this->model;
    }
} 
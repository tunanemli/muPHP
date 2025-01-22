<?php

namespace Core\Database\Exceptions;

use RuntimeException;

class ModelNotFoundException extends RuntimeException
{
    public function __construct(string $model, int|string $id)
    {
        parent::__construct("No query results for model [{$model}] with ID [{$id}].");
    }
} 
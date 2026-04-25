<?php
declare(strict_types=1);

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }
}

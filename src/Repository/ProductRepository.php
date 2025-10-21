<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Repository;

use Doctrine\DBAL\Connection;
use Raketa\BackendTestTask\Repository\Entity\Product;
use RuntimeException;

class ProductRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getByUuid(string $uuid): Product
    {
        $row = $this->connection->fetchAssociative(
            "SELECT * FROM products WHERE uuid = :uuid",
            ['uuid' => $uuid]
        );

        if (!$row) {
            throw new RuntimeException('Product not found');
        }

        return $this->make($row);
    }

    /**
     * @return Product[]
     */
    public function getByCategory(string $category): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT * FROM products WHERE is_active = 1 AND category = :category",
            ['category' => $category]
        );

        return array_map(
            fn(array $row) => $this->make($row),
            $rows
        );
    }

    public function make(array $row): Product
    {
        return new Product(
            (int)$row['id'],
            $row['uuid'],
            (bool)$row['is_active'],
            $row['category'],
            $row['name'],
            $row['description'],
            $row['thumbnail'],
            (float)$row['price']
        );
    }
}

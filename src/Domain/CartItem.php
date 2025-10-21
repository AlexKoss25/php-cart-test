<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain;

final class CartItem
{
    public function __construct(
        private string $uuid,
        private string $productUuid,
        private float $price,
        private int $quantity,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getProductUuid(): string
    {
        return $this->productUuid;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function increaseQuantity(int $amount): void
    {
        $this->quantity += $amount;
    }
}

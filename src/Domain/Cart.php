<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Domain;

/**
 * Cart — более гибкая модель: customer и paymentMethod опциональны.
 * Это сделано чтобы совмещать разные места создания корзины в проекте.
 */
final class Cart
{
    public function __construct(
        readonly private string $uuid,
        /** @var CartItem[] */
        private array $items = [],
        private ?Customer $customer = null,
        private ?string $paymentMethod = null,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /** @return CartItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(CartItem $item): void
    {
        $this->items[] = $item;
    }

    public function findItemByProductUuid(string $productUuid): ?CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getProductUuid() === $productUuid) {
                return $item;
            }
        }

        return null;
    }
}

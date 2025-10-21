<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\View;

use Raketa\BackendTestTask\Domain\Cart;
use Raketa\BackendTestTask\Repository\ProductRepository;

readonly class CartView
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function toArray(Cart $cart): array
    {
        $data = [
            'uuid' => $cart->getUuid(),
            'customer' => null,
            'payment_method' => $cart->getPaymentMethod(),
        ];

        $customer = $cart->getCustomer();
        if ($customer !== null) {
            $data['customer'] = [
                'id' => $customer->getId(),
                'name' => implode(' ', [
                    $customer->getLastName(),
                    $customer->getFirstName(),
                    $customer->getMiddleName(),
                ]),
                'email' => $customer->getEmail(),
            ];
        }

        $total = 0;
        $data['items'] = [];

        foreach ($cart->getItems() as $item) {
            // productRepository может выбросить исключение — по условию этого проекта
            // инфраструктура/валидация абстрагируются.
            $product = $this->productRepository->getByUuid($item->getProductUuid());

            $itemTotal = $item->getPrice() * $item->getQuantity();
            $total += $itemTotal;

            $data['items'][] = [
                'uuid' => $item->getUuid(),
                'price' => $item->getPrice(),
                'total' => $itemTotal,
                'quantity' => $item->getQuantity(),
                'product' => [
                    'id' => $product->getId(),
                    'uuid' => $product->getUuid(),
                    'name' => $product->getName(),
                    'thumbnail' => $product->getThumbnail(),
                    'price' => $product->getPrice(),
                ],
            ];
        }

        $data['total'] = $total;

        return $data;
    }
}

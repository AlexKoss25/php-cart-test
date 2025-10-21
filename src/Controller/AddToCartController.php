<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Domain\CartItem;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\Repository\ProductRepository;
use Raketa\BackendTestTask\View\CartView;
use Ramsey\Uuid\Uuid;

readonly class AddToCartController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartView $cartView,
        private CartManager $cartManager,
    ) {
    }

    /**
     * Добавление товара в корзину — POST
     * Предполагается, что базовая валидация body уже сделана фреймворком.
     */
    public function post(RequestInterface $request): ResponseInterface
    {
        $response = new JsonResponse();

        try {
            $raw = json_decode($request->getBody()->getContents(), true);
            $productUuid = $raw['productUuid'] ?? null;
            $quantity = isset($raw['quantity']) ? (int)$raw['quantity'] : 0;

            if (!$productUuid || $quantity <= 0) {
                $response = $response->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'Invalid input']));
                return $response;
            }

            $product = $this->productRepository->getByUuid($productUuid);
            if (!$product) {
                // По условию: если продукт не найден — 404
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(['error' => 'Product not found']));
                return $response;
            }

            $cart = $this->cartManager->getCart();

            $existing = $cart->findItemByProductUuid($productUuid);
            if ($existing) {
                $existing->increaseQuantity($quantity);
            } else {
                $cart->addItem(new CartItem(
                    Uuid::uuid4()->toString(),
                    $product->getUuid(),
                    (float)$product->getPrice(),
                    $quantity
                ));
            }

            // Сохранение корзины - CartManager отвечает за TTL и работу с Redis
            $this->cartManager->saveCart($cart);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'cart' => $this->cartView->toArray($cart),
            ]));

            return $response->withStatus(200);
        } catch (\Throwable $e) {
            // Логирование абстрагировано, если есть logger у CartManager — используем его
            if (property_exists($this->cartManager, 'logger') && $this->cartManager->logger) {
                try {
                    $this->cartManager->logger->error($e->getMessage(), ['exception' => $e]);
                } catch (\Throwable $ignore) {
                }
            }
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['error' => 'Internal server error']));
            return $response;
        }
    }
}

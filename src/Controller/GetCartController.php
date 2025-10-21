<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\Repository\CartManager;
use Raketa\BackendTestTask\View\CartView;

readonly class GetCartController
{
    public function __construct(
        public CartView $cartView,
        public CartManager $cartManager
    ) {
    }

    public function get(RequestInterface $request): ResponseInterface
    {
        $response = new JsonResponse();

        try {
            $cart = $this->cartManager->getCart();

            // По условию: если корзина отсутствует — фреймворк/инфраструктура должна обрабатывать,
            // тут возвращаем текущее состояние корзины как есть (может быть пустая).
            $response->getBody()->write(json_encode($this->cartView->toArray($cart)));
            return $response->withStatus(200);
        } catch (\Throwable $e) {
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

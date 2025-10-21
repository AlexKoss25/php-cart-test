<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Raketa\BackendTestTask\View\ProductsView;

readonly class GetProductsController
{
    public function __construct(
        private ProductsView $productsView
    ) {
    }

    /**
     * Получение списка товаров по категории.
     * Передача категории через query param: ?category=...
     * Базовая валидация запроса предполагается фреймворком.
     */
    public function get(RequestInterface $request): ResponseInterface
    {
        $response = new JsonResponse();

        try {
            $query = method_exists($request, 'getQueryParams') ? $request->getQueryParams() : [];
            $category = $query['category'] ?? null;

            if (!$category) {
                $response = $response->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'Category required']));
                return $response;
            }

            $data = $this->productsView->toArray($category);
            $response->getBody()->write(json_encode($data));
            return $response->withStatus(200);
        } catch (\Throwable $e) {
            // логирование абстрагируемся
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['error' => 'Internal server error']));
            return $response;
        }
    }
}

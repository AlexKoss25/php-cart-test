<?php

declare(strict_types=1);

namespace Raketa\BackendTestTask\Repository;

use Exception;
use Psr\Log\LoggerInterface;
use Raketa\BackendTestTask\Domain\Cart;
use Raketa\BackendTestTask\Infrastructure\ConnectorFacade;

class CartManager extends ConnectorFacade
{
    // logger может быть установлен через setLogger, или задан извне
    public ?LoggerInterface $logger = null;

    public function __construct($host, $port, $password)
    {
        parent::__construct($host, $port, $password, 1);
        parent::build();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Сохраняет корзину под ключом session_id()
     * CartManager отвечает за работу с TTL (через Connector::set)
     */
    public function saveCart(Cart $cart): void
    {
        try {
            $key = session_id() ?: (string)($cart->getUuid() ?? '');
            $this->connector->set($key, $cart);
        } catch (Exception $e) {
            if ($this->logger) {
                try {
                    $this->logger->error('Cart save error: ' . $e->getMessage(), ['exception' => $e]);
                } catch (\Throwable $ignore) {
                }
            }
            // по условию не выбрасываем дальше — инфра отвечает за retry/alerting
        }
    }

    /**
     * Возвращает Cart или пустую Cart если не найден/ошибка.
     */
    public function getCart(): Cart
    {
        try {
            $key = session_id();
            $cart = $this->connector->get($key);
            if (!$cart) {
                return new Cart($key ?? '', []);
            }
            return $cart;
        } catch (Exception $e) {
            if ($this->logger) {
                try {
                    $this->logger->error('Cart get error: ' . $e->getMessage(), ['exception' => $e]);
                } catch (\Throwable $ignore) {
                }
            }
            return new Cart(session_id() ?: '', []);
        }
    }
}

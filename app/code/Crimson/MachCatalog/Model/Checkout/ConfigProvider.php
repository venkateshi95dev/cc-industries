<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 5:55 PM
 * @brief
 */

namespace Crimson\MachCatalog\Model\Checkout;

use Crimson\Checkout\Model\Service\CartBackorders;
use Crimson\Checkout\Model\Service\IsTruckShip;
use Crimson\Checkout\Model\Service\IsCartDropship;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\MachBase\Model\MachConfig;

/**
 * Class ConfigProvider
 * @package Crimson\MachCatalog\Model\Checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    CONST XPATH_TRUCK_MESSAGE = 'checkout/cart/cart_truck_msg';
    CONST XPATH_TRUCK_MESSAGE_UNDER_SHIPPING_METHOD = 'checkout/cart/cart_truck_msg_under_shipping_method';

    /**
     * @var CartBackorders
     */
    protected $cartBackorders;

    /**
     * @var IsTruckShip
     */
    protected $isTruckShip;

    /**
     * @var IsCartDropship
     */
    protected $isCartDropship;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CheckoutSession
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        CartBackorders $cartBackorders,
        IsTruckShip $isTruckShip,
        IsCartDropship $isCartDropship,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession
    ) {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->cartBackorders = $cartBackorders;
        $this->isTruckShip = $isTruckShip;
        $this->isCartDropship = $isCartDropship;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig(): array
    {
        $config = [];
        if ($this->storeManager->getWebsite()->getCode() == MachConfig::ZIP_WEBSITE_CODE) {
            try {
                $config['has_backorders'] = $this->cartBackorders->doesCartHaveBackOrders($this->checkoutSession->getQuote());
                $config['is_dropship'] = $this->isCartDropship->doesCartHaveDropships($this->checkoutSession->getQuote());
                $config['is_truckship'] = $this->isTruckShip->doesCartHaveIsTruckShip($this->checkoutSession->getQuote());
                $config['is_truckship_message'] = (string)$this->scopeConfig->getValue(
                    self::XPATH_TRUCK_MESSAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $config['truck_ship_message_under_shipping_method'] = (string)$this->scopeConfig->getValue(
                    self::XPATH_TRUCK_MESSAGE_UNDER_SHIPPING_METHOD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            } catch (\Exception $e) {
                $config['has_backorders'] = false;
                $config['is_dropship'] = false;
                $config['is_truckship'] = false;
                $config['is_truckship_message'] = '';
            }
        }

        return $config;
    }
}

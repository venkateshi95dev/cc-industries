<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class responsible for providing store configurations
 */
class AdditionalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var CurrencyFactory
     */
    public $currencyfactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyfactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyfactory
    ) {
        $this->storeManager = $storeManager;
        $this->currencyfactory = $currencyfactory;
    }

    /**
     * Returns store configurations
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $code = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $symbol = $this->currencyfactory->create()->load($code);
        $output["currencySymbol"] = $symbol->getCurrencySymbol();
        $output["xcvcv"] = "test";
        return $output;
    }
}

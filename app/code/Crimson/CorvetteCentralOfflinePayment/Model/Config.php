<?php

namespace Crimson\CorvetteCentralOfflinePayment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH__PAYMENT_COD_HIDDEN = 'payment/cashondelivery/hidden';

    public function __construct(
        private ScopeConfigInterface                       $scopeConfig,
        private \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
    }

    public function codIsHidden(?int $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->isSetFlag(self::XML_PATH__PAYMENT_COD_HIDDEN, ScopeInterface::SCOPE_STORE, $storeId);
    }

}

<?php

namespace Crimson\Customer\Plugin\Block;

use Crimson\CokerWV\Model\Config;
use Magento\CustomAttributeManagement\Block\Form;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Crimson\ZipCokerWvConsolidation\Model\Config as ZipCokerWvConsolidationConfig;

class FormPlugin
{

    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {}

    /**
     * @param Form $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetUserDefinedAttributes(Form $subject, array $result): array
    {
        if ($this->storeManager->getWebsite()->getCode() == ZipCokerWvConsolidationConfig::ZIP_WEBSITE_CODE) {
            foreach ($result as $attrCode => $attrData) {
                if (in_array($attrCode, Config::COKER_WV_CUSTOMER_ATTRIBUTES)) {
                    unset($result[$attrCode]);
                }
            }
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Crimson\Theme\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Information;
use Magento\Store\Model\StoreManagerInterface;

class StoreInformation extends Template
{
    /**
     * StoreInformation constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private Information $storeInfo,
        private StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return string
     */
    public function getTelephone(): string
    {
        $store = $this->storeManager->getStore();
        $telephone = $this->storeInfo->getStoreInformationObject($store)->getPhone();

        if(!$telephone) {
            return '';
        }

        return $telephone;
    }

    /**
     * @return string
     */
    public function getTelephoneLink(): string
    {
        $telephone = $this->getTelephone();

        if (empty($telephone)) {
            return '';
        }

        $formattedTelephone = preg_replace('/[^+\d]/', '', $telephone);

        return 'tel:+' . $formattedTelephone;
    }
}

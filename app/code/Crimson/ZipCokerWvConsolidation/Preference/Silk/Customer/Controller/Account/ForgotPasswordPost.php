<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Silk\Customer\Controller\Account;

use Crimson\ZipCokerWvConsolidation\Model\Config;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * ForgotPasswordPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost
{
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        private readonly StoreManagerInterface $storeManager,
        private readonly \Crimson\Customer\Controller\Account\ForgotPasswordPost $crimsonForgotPasswordPost
    ) {
        parent::__construct($context,$customerSession,$customerAccountManagement,$escaper);
    }

    public function execute()
    {
        if ($this->storeManager->getWebsite()->getCode() === Config::ZIP_WEBSITE_CODE) {
            return $this->crimsonForgotPasswordPost->execute();
        }

        return parent::execute();
    }
}

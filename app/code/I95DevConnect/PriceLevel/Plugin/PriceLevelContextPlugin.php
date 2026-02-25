<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;

/**
 * Plugin class to access customer price level through out the context
 */
class PriceLevelContextPlugin
{
    public const PRICELEVEL_CONTEXT = "customer_pricelevel";
    /**
     * @var Session
     */
    public $customerSession;

    /**
     * Class constructor to include all the dependencies
     *
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * \Magento\Framework\App\Http\Context::getVaryString is used by Magento to retrieve unique identifier
     *
     * For selected context,so this is a best place to declare custom context variables
     *
     * @param Context $subject
     */
    public function beforeGetVaryString(Context $subject)
    {
        $pricelevel = $this->customerSession->getCustomer()->getPricelevel();
        if ($pricelevel != '') {
            $subject->setValue(self::PRICELEVEL_CONTEXT, $pricelevel, '');
        }
    }
}

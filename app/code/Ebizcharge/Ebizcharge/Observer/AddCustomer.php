<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


/**
 * Observe and upload customer to EConnect automatically
 *
 * Class Addcustomer
 */
class AddCustomer extends AbstractModel implements ObserverInterface
{
    /**
     * Sync customer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {

        /** check if Ebizcharge is Enabled and Active */
        if (!$this->isModuleActive()) {
            /** Ebizcharge Payment Method is not active logging to the logger */
            $this->ebizchargeLogger->addInfo(__('EbizCharge module is not active from system configuration'));
            return;
        }

        /** @var $customer get customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($customer && $customer->getId()) {
            /** @var  $customerId */
            $customerId = $customer->getId() ?? 0;
            $isDownload = $this->sessionManagerInterface->getIsDownload() ?? false;

            if (!$isDownload) {
                /** sync customer to Ebizcharge */
             //   $this->customerFactory->create()->syncLocalCustomerToEbizhcarge($customerId);

                /** syncing of the customer is logged to the logger */
                $this->ebizchargeLogger->addInfo(__('Success Customer with customer id: ' . $customerId .
                    ' is synced to EbizCharge via Event Observer'));
            }
        }
    }
}

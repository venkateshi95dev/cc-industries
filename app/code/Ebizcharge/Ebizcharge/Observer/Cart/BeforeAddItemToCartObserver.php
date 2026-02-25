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

namespace Ebizcharge\Ebizcharge\Observer\Cart;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Observer on Before Add Item To Cart
 *
 * Class BeforeAddItemToCartObserver
 */
class BeforeAddItemToCartObserver extends AbstractModel implements ObserverInterface
{

    /**
     * Execute Method
     *
     * @param EventObserver $observer
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive && $this->isRecurringEnabled($this->getStoreId())) {

            try {

                /** @var  $productParams */
                $productParams = $this->request->getParams();

                if ($this->isRecurringExist($productParams, $this->getStoreId())) {

                    /**
                     * Checking if Product already exists
                     */
                    $isProductSubscribed = $this->_quoteFactory->create()->isSubscriptionAlreadyExists($productParams);
                    /**
                     * if Is Subscription against this product already exists
                     */
                    if ($isProductSubscribed) {
                        $this->request->setParam('product', false);
                        $this->request->setParam('return_url', false);

                        // phpcs:disable
                        $this->messageManagerInterface->addErrorMessage(__('Subscription already exist between selected dates.
            Please choose any other subscription dates or edit existing subscriptions. Please browse to:  (My Account -> Manage Subscriptions)'));

                        $this->ebizchargeLogger->addCritical(__($productParams['product'] . " - This product already subscribed, please try to edit it in My Accounts "));
                        // phpcs:enable
                    }
                }


            } catch (\Exception $exception) {
                $this->ebizchargeLogger->addCritical(__("Exception occurred during adding subscriptions, please try to edit it in My Accounts "));
                // phpcs:disable
                $this->messageManagerInterface->addErrorMessage(__('An known error occurred to subscribe for the selected dates.
            Please choose any other subscription dates or edit existing subscriptions. Please browse to:  (My Account -> Manage Subscriptions)'));
            }
        }
    }

}

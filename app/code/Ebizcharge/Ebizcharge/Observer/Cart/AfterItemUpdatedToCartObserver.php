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
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Observer class on After Item Updated To Cart
 *
 * Class AfterItemUpdatedToCartObserver
 */
class AfterItemUpdatedToCartObserver extends AbstractModel implements ObserverInterface
{

    /**
     * Execute Method
     *
     * @param EventObserver $observer
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        /** @var  $productParams */
        $productParams = $this->request->getParams();
        $cartObject = $observer->getEvent();
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive && $this->isRecurringEnabled($this->getStoreId())) {
            /**
             * Update Product Qty in Cart
             */
            $updateProductQtyInCart = $this->_quoteFactory->create()->updateSubscribedProductsQty($cartObject);
        }
    }
}

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

namespace Ebizcharge\Ebizcharge\Plugin\Sales\Order\AdminOrder\CreateOrder;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\AdminOrder\Create;

class UpdateAddressRegion extends AbstractModel
{

    /**
     *
     *
     * @param Create $subject
     * @param $postData
     * @return array[]
     * @throws NoSuchEntityException
     */
    public function beforeImportPostData(Create $subject, $postData)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            if (isset($postData["billing_address"])) {
                $billingRegionId = isset($postData["billing_address"]["region_id"]) && $postData["billing_address"]["region_id"] ? $postData["billing_address"]["region_id"] : "";
                $BillingRegion = isset($postData["billing_address"]["region"]) && $postData["billing_address"]["region"] ? $postData["billing_address"]["region"] : $billingRegionId;
                $postData["billing_address"]["region"] = $BillingRegion;
            }

            if (isset($postData["shipping_address"])) {
                $shippingRegionId = isset($postData["shipping_address"]["region_id"]) && $postData["shipping_address"]["region_id"] ? $postData["shipping_address"]["region_id"] : "";
                $shippingRegion = isset($postData["shipping_address"]["region"]) && $postData["shipping_address"]["region"] ? $postData["shipping_address"]["region"] : $shippingRegionId;
                $postData["shipping_address"]["region"] = $shippingRegion;
            }
        }

        return [$postData];
    }
}

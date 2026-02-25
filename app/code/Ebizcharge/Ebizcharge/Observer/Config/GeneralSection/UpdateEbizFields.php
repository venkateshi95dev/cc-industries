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

namespace Ebizcharge\Ebizcharge\Observer\Config\GeneralSection;

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Update EBizCharge field "Unit Of Measure"
 *
 * Class UpdateEbizFields
 */
class UpdateEbizFields extends AbstractModel implements ObserverInterface
{


    /**
     * Update "Unit Of Measure" field if Magento core "Weight Unit" field changed
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        if($isEbizChargeActive) {
            $website = $observer->getEvent()->getWebsite();
            $store = $observer->getEvent()->getStore();
            $changedPaths = (array)$observer->getEvent()->getChangedPaths();

            if (\array_intersect([
                Data::XML_PATH_WEIGHT_UNIT,
            ], $changedPaths)) {
                $params = $this->request->getParam('groups');
                $weightUnit = $params['locale']['fields']['weight_unit']['value'] ?? '';

                $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                $scopeId = 0;
                if ($website != '') {
                    $scope = ScopeInterface::SCOPE_WEBSITES;
                    $scopeId = (int)$website;
                } elseif ($store != '') {
                    $scope = ScopeInterface::SCOPE_STORES;
                    $scopeId = (int)$store;
                }

                // If value is not present then delete it as when default scope is checked then
                // value is empty & magento itself delete the value from database
                if ($weightUnit) {
                    $this->configWriter->save(
                        ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_WEIGHT_UNIT,
                        $weightUnit,
                        $scope,
                        $scopeId
                    );
                } else {
                    $this->configWriter->delete(
                        ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_WEIGHT_UNIT,
                        $scope,
                        $scopeId
                    );
                }
            }
        }
    }
}

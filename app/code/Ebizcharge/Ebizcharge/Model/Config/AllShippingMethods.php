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

namespace Ebizcharge\Ebizcharge\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Shipping\Model\ConfigFactory;
use Magento\Store\Model\ScopeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;

/**
 * Shipping methods class
 *
 * Class AllShippingMethods
 */
// phpcs:ignore
class AllShippingMethods implements OptionSourceInterface
{
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $_shippingConfigFactory;


    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigFactory $shippingConfigFactory
     * @param EbizchargeLogger $bizchargeLogger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigFactory $shippingConfigFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  _scopeConfig */
        $this->_scopeConfig = $scopeConfig;
        /** @var  _shippingConfigFactory */
        $this->_shippingConfigFactory = $shippingConfigFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Return array of carriers.
     *
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $methods[] = ['value' => '', 'label' => 'Please select shipping method'];

        try {
            $carriers = $this->_shippingConfigFactory->create()->getActiveCarriers();

            if($carriers && count($carriers) > 0) {
                foreach ($carriers as $carrierCode => $carrierModel) {
                    $carrierTitle = $this->_scopeConfig->getValue(
                        'carriers/' . $carrierCode . '/title',
                        ScopeInterface::SCOPE_STORE
                    );
                    $methods[$carrierCode] = [
                        'value' => $carrierCode . '_' . $carrierCode,
                        'label' => $carrierTitle . ' [' . $carrierCode . ']',
                    ];
                }
            }
        }catch (\Exception $exception){
            $this->ebizchargeLogger->addCritical(__("Please enable shipping inventory modules from the Magento Config and try again. ".$exception->getMessage()));

            $methods[] = ['value' => 'NA', 'label' => 'Shipping Modules are not available'];
        }

        return $methods;
    }
}

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

namespace Ebizcharge\Ebizcharge\Model\Source\PaymentOptions;

use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Credit Card Payment Options Model
 *
 * Class CreditCardPayment
 */
class CreditCardPayment implements OptionSourceInterface
{
    /**
     * @var EbizConfigFactory
     */
    protected EbizConfigFactory $configFactory;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $requestInterface;

    /**
     * @var TranApi
     */
    protected TranApi $tranApi;

    /**
     * CreditCardPayment constructor
     *
     * @param TranApi $tranApi
     * @param EbizConfigFactory $configFactory
     * @param RequestInterface $requestInterface
     */
    public function __construct(
        TranApi $tranApi,
        EbizConfigFactory $configFactory,
        RequestInterface $requestInterface
    )
    {
        /** @var $tranApi */
        $this->tranApi = $tranApi;
        /** @var $configModel */
        $this->configFactory = $configFactory;
        /** @var $requestInterface */
        $this->requestInterface = $requestInterface;
    }


    /**
     * To Option Array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        $configFactory = $this->configFactory->create() ;
        $storeId = $configFactory->getStoreId();
        $storeId = $this->requestInterface->getParam("store") ?? $storeId;

        /** @var  $paymentTypes */
        /** @var  $paymentTypes */
        if ($configFactory->isActive($storeId)) {
            $paymentTypes = $this->tranApi->getMerchantTransactionInfo($storeId);

            /** update the payment Types in the config Panel */
          //  $this->_dataModel->updateConfigPaymentTypes($paymentTypes);

            if (isset($paymentTypes['AllowCreditCardPayments']) &&
                $paymentTypes['AllowCreditCardPayments'] !== false) {
                return [
                    [
                        'value' => 1,
                        'label' => __('Yes')
                    ],
                    [
                        'value' => 0,
                        'label' => __('No')
                    ]
                ];
            }
        }
        return [
            [
                'value' => 0,
                'label' => __('No (Disabled on the EBizCharge)')
            ]
        ];
    }
}

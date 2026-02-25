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

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Credit Card Payment Options Model
 *
 * Class CreditCardPayment
 */
class PaymentFormTypes implements OptionSourceInterface
{


    /**
     * @var Config
     */
    protected Config $configModel;
    /**
     * @var RequestInterface
     */
    protected RequestInterface $requestInterface;

    /**
     * @var TranApi
     */
    private TranApi $tranApi;

    /**
     * CreditCardPayment constructor
     *
     * @param TranApi $tranApi
     * @param Config $configModel
     * @param RequestInterface $requestInterface
     */
    public function __construct(
        TranApi $tranApi,
        Config $configModel,
        RequestInterface $requestInterface
    )
    {
        /** @var $tranApi */
        $this->tranApi = $tranApi;
        /** @var $configFactory */
        $this->configModel = $configModel;
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
        $configModel = $this->configModel ;
        $storeId = $configModel->getStoreId();
        $storeId = $this->requestInterface->getParam("store") ?? $storeId;
        /** @var  $paymentTypes */
        if ($configModel->isActive($storeId)) {
            $paymentTypes = $this->tranApi->getMerchantTransactionInfo($storeId);

            /** update the payment Types in the config Panel */

            if (isset($paymentTypes['AllowCreditCardPayments']) && $paymentTypes['AllowCreditCardPayments'] === true)
            {
                return [
                    [
                        'value' => 1,
                        'label' => __('Integrated Payment Options')
                    ],
                    /*  * By default disabled Web Hosted Form **/
                    [
                        'value' => 2,
                        'label' => __('Hosted Payments Web Form')
                    ]

                ];
            } else {
                return [
                    [
                        'value' => 0,
                        'label' => __('No (Disabled on the EBizCharge)')
                    ]
                ];


            }
        } else {
            return [
                [
                    'value' => 0,
                    'label' => __('No (Disabled on the EBizCharge)')
                ]
            ];
        }


    }
}

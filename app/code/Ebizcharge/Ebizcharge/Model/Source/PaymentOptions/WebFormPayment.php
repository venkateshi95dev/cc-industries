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

use Ebizcharge\Ebizcharge\Model\Data;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Web Form Payment Options Model
 *
 * Class WebFormPayment
 */
class WebFormPayment implements OptionSourceInterface
{
    /**
     * @var TranApi
     */
    private TranApi $tranApi;


    /**
     * WebFormPayment constructor.
     *
     * @param TranApi $tranApi
     */
    public function __construct(
        TranApi $tranApi
    )
    {
        /** @var  tranApi */
        $this->tranApi = $tranApi;

    }

    /**
     * To Option Array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        /** @var  $paymentTypes */
        $paymentTypes = $this->tranApi->getMerchantTransactionInfo();

        if (isset($paymentTypes['AllowACHPayments']) && $paymentTypes['AllowACHPayments'] !== false) {
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
        return [
            [
                'value' => 0,
                'label' => __('No (Disabled on the EBizCharge)')
            ]
        ];
    }
}

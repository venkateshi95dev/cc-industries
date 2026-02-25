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

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;

/**
 * Model Class Abstract Payment
 *
 * Class AbstractPaymentModel
 */
abstract class AbstractPaymentModel extends \Magento\Payment\Model\Method\AbstractMethod implements PaymentInterface
{



    /**
     * Ach Payment Options Types
     */
    public const ACH_PAYMENT_OPTION_TYPES = [
        PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_CHECKING =>
            PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_CHECKING,
        PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_SAVINGS =>
            PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_SAVINGS
    ];

    /**
     * CVV2 Payment Messages array
     *
     * @var array $cvv2PaymentMessages
     */
    public array $cvv2PaymentMessages = [];

    /**
     * AVS Payment Messages array
     *
     * @var array $avsPaymentMessages
     */
    public array $avsPaymentMessages = [];

    /**
     * @var array|string[]
     */
    public array $validAvsResultCodes = [];

    /**
     * Payment Info Params array
     *
     * @var array
     */
    protected array $paymentInfoParams = [];

    /**
     * AVS card code Responses array
     *
     * @var array
     */
    protected array $avsCardCodePaymentResponses = [];

    /**
     * Cvv Card Code Responses array
     *
     * @var array
     */
    protected array $cvvCardCodePaymentResponses = [];

    /**
     * Cavv Card Code Payment Responses array
     *
     * @var array
     */
    protected array $cavvCardCodePaymentResponses = [];

    /**
     * Decline Card Code Payment Responses array
     *
     * @var array|int[]
     */
    protected array $declineCardCodePaymentResponses = [];

    /**
     * AbstractPaymentModel constructor.
     */
    public function __construct()
    {
        /** @var validAvsResultCodes */
        $this->validAvsResultCodes = ["YYY", "Y", "YYA", "YYD"];

        /**
         * CVV2 Payment Messages
         */
        $this->cvv2PaymentMessages = [
            PaymentInterface::CVV2_CARD_CODE_RESULT_M => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_M,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_M,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_M
            ],
            PaymentInterface::CVV2_CARD_CODE_RESULT_N => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_N,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_N,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_N
            ],
            PaymentInterface::CVV2_CARD_CODE_RESULT_P => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_P,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_P,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_P
            ],
            PaymentInterface::CVV2_CARD_CODE_RESULT_S => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_S,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_S,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_S
            ],
            PaymentInterface::CVV2_CARD_CODE_RESULT_U => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_U,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_U,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_U
            ],
            PaymentInterface::CVV2_CARD_CODE_RESULT_X => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_X,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_X,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_X
            ],
            PaymentInterface::CVV2_CARD_CODE_RESULT_BLANK => [
                'code' => PaymentInterface::CVV2_CARD_CODE_RESULT_BLANK,
                'msg' => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_BLANK,
                'label' => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_BLANK
            ]
        ];
        /**
         * AVS Payment Messages
         */
        $this->avsPaymentMessages = [
            PaymentInterface::AVS_CARD_CODE_RESULT_BLANK => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_BLANK,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_BLANK,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_BLANK
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_YYY => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_YYY,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYY,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYY
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_NYZ => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_NYZ,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_NYZ,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_NYZ
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_YNA => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_YNA,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_YNA,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YNA
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_NNN => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_NNN,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_NNN,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_NNN
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_YYX => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_YYX,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYX,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYX
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_NYW => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_NYW,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_NYW,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYW
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_XXW => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_XXW,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXW,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXW
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_XXU => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_XXU,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXU,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXU
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_XXR => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_XXR,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXR,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXR
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_XXS => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_XXS,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXS,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXS
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_XXE => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_XXE,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXE,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXE
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_XXG => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_XXG,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXG,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXG
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_YYG => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_YYG,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYG,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYG
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_GGG => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_GGG,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_GGG,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_GGG
            ],
            PaymentInterface::AVS_CARD_CODE_RESULT_YGG => [
                'code' => PaymentInterface::AVS_CARD_CODE_RESULT_YGG,
                'msg' => PaymentInterface::AVS_CARD_CODE_RESULT_DISPLAY_MSG_YGG,
                'label' => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YGG
            ]
        ];

        /**
         * AVS Card Code Payment Responses
         */
        $this->avsCardCodePaymentResponses = [
            PaymentInterface::AVS_CARD_CODE_RESULT_BLANK => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_BLANK,
            PaymentInterface::AVS_CARD_CODE_RESULT_YYY => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYY,
            PaymentInterface::AVS_CARD_CODE_RESULT_NYZ => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_NYZ,
            PaymentInterface::AVS_CARD_CODE_RESULT_YNA => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YNA,
            PaymentInterface::AVS_CARD_CODE_RESULT_NNN => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_NNN,
            PaymentInterface::AVS_CARD_CODE_RESULT_YYX => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYX,
            PaymentInterface::AVS_CARD_CODE_RESULT_NYW => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYW,
            PaymentInterface::AVS_CARD_CODE_RESULT_XXW => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXW,
            PaymentInterface::AVS_CARD_CODE_RESULT_XXU => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXU,
            PaymentInterface::AVS_CARD_CODE_RESULT_XXR => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXR,
            PaymentInterface::AVS_CARD_CODE_RESULT_XXS => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXS,
            PaymentInterface::AVS_CARD_CODE_RESULT_XXE => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXE,
            PaymentInterface::AVS_CARD_CODE_RESULT_XXG => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_XXG,
            PaymentInterface::AVS_CARD_CODE_RESULT_YYG => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YYG,
            PaymentInterface::AVS_CARD_CODE_RESULT_GGG => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_GGG,
            PaymentInterface::AVS_CARD_CODE_RESULT_YGG => PaymentInterface::AVS_CARD_CODE_RESULT_MSG_LABEL_YGG
        ];

        /**
         * CVV Card Code Payment Responses
         */
        $this->cvvCardCodePaymentResponses = [
            PaymentInterface::CVV2_CARD_CODE_RESULT_M => PaymentInterface::CVV2_CARD_CODE_RESULT_DISPLAY_MSG_M,
            PaymentInterface::CVV2_CARD_CODE_RESULT_N => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_N,
            PaymentInterface::CVV2_CARD_CODE_RESULT_P => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_P,
            PaymentInterface::CVV2_CARD_CODE_RESULT_S => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_S,
            PaymentInterface::CVV2_CARD_CODE_RESULT_U => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_U,
            PaymentInterface::CVV2_CARD_CODE_RESULT_X => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_X,
            PaymentInterface::CVV2_CARD_CODE_RESULT_BLANK => PaymentInterface::CVV2_CARD_CODE_RESULT_MSG_LABEL_BLANK
        ];

        /**
         * CAVV Card Payment Responses
         */
        $this->cavvCardCodePaymentResponses = [
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_1 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_1,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_2 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_2,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_3 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_3,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_4 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_4,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_5 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_5,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_6 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_6,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_7 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_7,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_8 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_8,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_9 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_9,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_A => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_A,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_B => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_B,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_C => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_C,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_D => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_D,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_G => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_H => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_H,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_I => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_H,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_K => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_I,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_S => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_K,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_U => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_U,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_G1 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G1,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_G2 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G2,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_J1 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J1,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_J2 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J2,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_J3 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J3,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_J4 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J4,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_K1 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_K1,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_S1 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S1,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_S2 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S2,
            PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_S3 => PaymentInterface::CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S3
        ];

        /** @var
         * declineCardCodePaymentResponses
         */
        $this->declineCardCodePaymentResponses = [
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_ => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_04 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_04,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_05 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_05,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_12 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_12,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_15 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_15,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_25 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_25,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_51 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_51,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_55 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_55,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_57 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_57,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_62 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_62,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_65 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_65,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_75 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_75,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_78 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_78,
            PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_97 => PaymentInterface::DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_97
        ];
    }
}

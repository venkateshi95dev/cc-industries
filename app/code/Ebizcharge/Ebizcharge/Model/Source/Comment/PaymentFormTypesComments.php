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

namespace Ebizcharge\Ebizcharge\Model\Source\Comment;

use Ebizcharge\Ebizcharge\Api\Data\CommentsInterface;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Config\Model\Config\CommentInterface;

/**
 * Credit Card Comment Source MOdel
 *
 * Class CreditCardComment
 */
class PaymentFormTypesComments implements CommentInterface, CommentsInterface
{
    /**
     * @var TranApi
     */
    private TranApi $tranApi;

    /**
     * CreditCardComment constructor.
     *
     * @param TranApi $tranApi
     */
    public function __construct(TranApi $tranApi)
    {
        /** @var  tranApi */
        $this->tranApi = $tranApi;
    }

    /**
     * Get comment Text
     *
     * @param mixed $elementValue
     * @return \Magento\Framework\Phrase|string
     */
    public function getCommentText($elementValue)
    {
        /** @var  $paymentTypes */
        $paymentTypes = $this->tranApi->getMerchantTransactionInfo();

        if (isset($paymentTypes['AllowCreditCardPayments']) && $paymentTypes['AllowCreditCardPayments'] !== false) {
            return __("Select \"Integrated Payment Option\" to integrate payment options or \"Hosted Payments Web Form\" to integrate Hosted web form options at checkout.");
        }
    }
}

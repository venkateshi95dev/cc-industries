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


namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Recurring Suspend Action
 *
 * Class SuspendAction
 */
class SuspendAction extends Action
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_suspend';

    /**
     * Wrong Request
     *
     * @const WRONG_REQUEST
     */
    public const WRONG_REQUEST = 1;

    /**
     * Wrong Token
     *
     * @const WRONG_TOKEN
     */
    public const WRONG_TOKEN = 2;

    /**
     * Action Exception
     *
     * @const ACTION_EXCEPTION
     */
    public const ACTION_EXCEPTION = 3;

    /**
     * @var TranApi
     */
    private $_tran;

    /**
     * @var EbizchargeLogger
     */
    private $ebizchargeLogger;

    /**
     * @param Context $context
     * @param TranApi $tranApi
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        TranApi $tranApi,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        $this->_tran = $tranApi;

        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute suspend action
     *
     * @return void
     */
    public function execute()
    {
        $ueSecurityToken = $this->_tran->getUeSecurityToken();
        $client = $this->_tran->getClient();

        $response = $client->SearchRecurringPayments(
            [
                'securityToken' => $ueSecurityToken,
                'fromDateTime' => '2019-10-01',
                'toDateTime' => '2028-12-30',
                'start' => 0,
                'limit' => 1000,
            ]
        );

        $recPayment = $response->SearchRecurringPaymentsResult->Payment;
        $decline = [];

        $i = 0;

        if (!empty($recPayment)) {
            foreach ($recPayment as $payment) {

                $responseGetTransactionDetails = $client->GetTransactionDetails(
                    [
                        'securityToken' => $ueSecurityToken,
                        'transactionRefNum' => $payment->RefNum,
                    ]
                );

                $GetGetTransactionDetailsResult = $responseGetTransactionDetails->GetTransactionDetailsResult;
                $paymentRes = $GetGetTransactionDetailsResult->Response;

                if ($paymentRes->ResultCode == 'D') {
                    $decline[] = $payment->PaymentMethod . '-' . $payment->Last4 . '-' . 'Declined' .
                        '+++' . $payment->ScheduledPaymentInternalId;
                }
            }
        }

        if (!empty($recPayment)) {
            foreach (array_count_values($decline) as $key => $val) {
                if ($val > 2) {
                    $explodeArray = explode("+++", $key);
                    $inernalSheduleId = $explodeArray[1];
                    $params = [
                        'securityToken' => $ueSecurityToken,
                        'scheduledPaymentInternalId' => $inernalSheduleId,
                        'statusId' => 1,
                    ];

                    $client->ModifyScheduledRecurringPaymentStatus($params);
                }
            }
        }
    }
}

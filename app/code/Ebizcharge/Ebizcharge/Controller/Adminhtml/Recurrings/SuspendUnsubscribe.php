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
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory;

/**
 * Recurring Suspend Unsubscribe
 *
 * Class SuspendUnsubscribe
 */
class SuspendUnsubscribe extends Action
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_suspend_unsubscribe';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var EbizchargeLogger
     */
    private $ebizchargeLogger;

    /**
     * @var RecurringFactory
     */
    private RecurringFactory $recurringFactory;

    /**
     * SuspendUnsubscribe constructor.
     *
     * @param Context $context
     * @param RecurringFactory $recurringFactory
     * @param CollectionFactory $collectionFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Filter $filter
     */
    public function __construct(
        Context           $context,
        RecurringFactory  $recurringFactory,
        CollectionFactory $collectionFactory,
        EbizchargeLogger  $ebizchargeLogger,
        Filter            $filter
    )
    {
        parent::__construct($context);

        /** @var  collectionFactory */
        $this->collectionFactory = $collectionFactory;
        /** @var  filter */
        $this->filter = $filter;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  recurringFactory */
        $this->recurringFactory = $recurringFactory;
    }

    /**
     * Execute Method
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $subscriptionResponse = [
            'success' => [],
            'failure' => []
        ];

        $recurringParams = $this->getRequest()->getParams();
        /** @var  $suspendedSubcibeResponse */
        $subscriptionResponseItems = $this->recurringFactory->create()->suspendUnsubscribeRecurrings($recurringParams);


        if (is_array($subscriptionResponseItems) && count($subscriptionResponseItems) > 0) {
            foreach ($subscriptionResponseItems as $subscriptionResponseItem) {

                    if (isset($subscriptionResponseItem["error"]) && $subscriptionResponseItem["error"] === true) {
                        $failureProducts = $subscriptionResponseItem["recurring_products"] ?? "";
                            $subscriptionResponse["failure"][] = $failureProducts;
                    }else{
                        $successProducts = $subscriptionResponseItem["recurring_products"] ?? "";
                        if (!empty($successProducts)) {
                            $subscriptionResponse["success"][] = $successProducts;
                        }
                    }

            }
        }

        if (count($subscriptionResponse["failure"]) > 0) {
            $errorMessage = "Error could not update the selected subscriptions ";
            $subscribedProducts = implode(',', $subscriptionResponse["failure"]); 

            if (!empty($subscribedProducts)) {
                if(strpos($subscribedProducts,",") !== false){
                    $subscribedProducts = substr($subscribedProducts, 1);
                }
                $errorMessage .= "\"" . $subscribedProducts . "\".";

            } else {
                $errorMessage .= ".";
            }
            $this->messageManager->addErrorMessage(__($errorMessage));
        }

        if (count($subscriptionResponse["success"]) > 0) {
            $successMessage = "Selected subscriptions are updated successfully ";
            $subscribedProducts = implode(',', $subscriptionResponse["success"]);
            if (!empty($subscribedProducts)) {
                $successMessage .= "\"" . $subscribedProducts . "\".";
            } else {
                $successMessage .= ".";
            }
            $this->messageManager->addSuccessMessage(__($successMessage));
        }


        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }
}

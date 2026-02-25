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
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Create the recurring orders
 *
 * Class CreateOrderAction
 */
class CreateOrderAction extends Action
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_add';

    /**
     * @var TranApi
     */
    protected TranApi $tranApi;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @param Context $context
     * @param Config $config
     * @param TranApi $tranApi
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        Config $config,
        TranApi $tranApi,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  config */
        $this->config = $config;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * @return bool|ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $request = $this->_request;

        if ($this->config->isEbizchargeActive() == 0) {
            $this->ebizchargeLogger->addError(__("Failed: EBizCharge module is inactive."));
            return $this->createErrorResponse('Failed: EBizCharge module is inactive.');
        }

        if ($this->config->isRecurringEnabled() == 0) {
            $this->ebizchargeLogger->addError(__("Failed: EBizCharge module is inactive."));
            return $this->createErrorResponse('Failed: EBizCharge recurring functionality is inactive.');
        }

        $startDate = $request->getParam('start_date');
        $this->ebizchargeLogger->addInfo(__('CreateOrder run start. The time is ' . date("Y-m-d h:i:sa")));
        $startDate = date('y-m-d');

        // phpcs:ignore
        echo '1'; exit;
    }

    /**
     * Creates an error message, and passes it to the "Manage
     *
     * @param mixed $errorMessage
     * @return bool
     */
    private function createErrorResponse($errorMessage)
    {
        $this->messageManager->addErrorMessage($errorMessage);
        return true;
    }
}

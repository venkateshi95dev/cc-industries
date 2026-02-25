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

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Recurring Load Customer addresses action
 *
 * Class LoadCustomerAddressAction
 */
class LoadCustomerAddressAction extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $_resultJsonFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * LoadCustomerAddressAction constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerFactory $customerFactory
    ) {
        parent::__construct($context);

        /** @var  resultJsonFactory */
        $this->_resultJsonFactory = $resultJsonFactory;
        /** @var  customerAddress */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Execute Method
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');

        if ($customerId) {
            $addresses = $this->_customerFactory->create()->getCustomerAddressList($customerId);
            return $this->_resultJsonFactory->create()
                ->setData(['html_data' => $addresses]);
        }
        return $this->_resultJsonFactory->create()
            ->setData(['html_data' => null]);
    }
}

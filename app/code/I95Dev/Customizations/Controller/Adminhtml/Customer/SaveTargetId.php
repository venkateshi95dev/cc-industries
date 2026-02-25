<?php

namespace I95Dev\Customizations\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Save Target Customer ID Controller
 */
class SaveTargetId extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $customerId = $this->getRequest()->getParam('customer_id');
            $targetCustomerId = $this->getRequest()->getParam('target_customer_id');
            
            if (!$customerId) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Customer ID is required.')
                ]);
            }
            
            $customer = $this->customerFactory->create()->load($customerId);
            
            if (!$customer->getId()) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Customer not found.')
                ]);
            }
            
            $customer->setData('target_customer_id', $targetCustomerId);
            $customer->save();
            
            return $resultJson->setData([
                'success' => true,
                'message' => __('Target Customer ID has been updated successfully.')
            ]);
            
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
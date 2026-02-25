<?php
namespace Crimson\LoginRegisterModal\Controller\Validate;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Validate
 * @package Crimson\LoginRegisterModal\Controller\Validate
 */
class Validate extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Customer
     */
    protected $_customerModel;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Customer $customerModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerModel = $customerModel;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $email = $this->getRequest()->getParam('email');
        $customerData = $this->_customerModel->getCollection()
            ->addFieldToFilter('email', $email);
        if(!count($customerData)) {
            $resultJson->setData('true');
        } else {
            $resultJson->setData(__('That email is already taken, try another one') );
        }
        return $resultJson;
    }
}

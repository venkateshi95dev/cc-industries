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

namespace Ebizcharge\Ebizcharge\Controller\PciCompliance;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Add New Method Response Action Class
 *
 * Class AddNewMethodResponse
 */
class AddNewMethodResponse extends Action
{
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var JsonFactory
     */
    private $_jsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context             $context,
        JsonFactory         $jsonFactory,
        CustomerSession     $customerSession,
    ) {
        /** Parent Constructor */
        parent::__construct($context);

        $this->_jsonFactory = $jsonFactory;
        $this->_customerSession = $customerSession;
    }

    /**
     * Set PCI compliance new payment method addition data in session
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        /** @var $jsonFactory */
        $jsonFactory = $this->_jsonFactory->create();
        $requestParams = $this->getRequest()->getParams();

        if (isset($requestParams['payment_method_id']) && $requestParams['payment_method_id']) {
            $pciResponse = [
                'error' => !(isset($requestParams['error']) && $requestParams['error'] == 'false'),
                'message' => $requestParams['message'] ?? '',
                'payment_method_id' => $requestParams['payment_method_id'],
                'response' => [
                    'method_name' => $requestParams['response']['method_name'] ?? ''
                ]
            ];
            $this->_customerSession->setPciAddNewMethodResponse($pciResponse);
        }

        $jsonFactory->setData(['error' => false]);
        return $jsonFactory;
    }
}

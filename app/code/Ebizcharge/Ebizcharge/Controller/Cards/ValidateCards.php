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

namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Validate Credit Cards
 *
 * Class ValidateCards
 */
class ValidateCards extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $_pageFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_jsonFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * ValidateCards constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $jsonFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory,
        EbizchargeLogger $ebizchargeLogger,
        CustomerFactory $customerFactory
    ) {
        /** @var  _pageFactory */
        $this->_pageFactory = $pageFactory;
        /** @var  _jsonFactory */
        $this->_jsonFactory = $jsonFactory;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;

         parent::__construct($context);
    }

    /**
     * Execute JSON Response
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        /** @var  $resultJson */
        $resultJson = $this->_jsonFactory->create();

        /** @var  $jsonResponse */
        $jsonResponse = [];

        return $resultJson->setData(
            [
                'json_data' => $jsonResponse
            ]
        );
    }
}

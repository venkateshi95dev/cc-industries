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

namespace Ebizcharge\Ebizcharge\Controller\Searchbox;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Searchbox Suggession Processor Action class
 *
 * Class SuggessionProcessor
 */
class SuggessionProcessor extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var EbizchargeLogger
     */
    protected $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Product
     */
    protected $_productModel;

    /**
     * SuggessionProcessor constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param CustomerFactory $customerFactory
     * @param Product $productModel
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        CustomerFactory $customerFactory,
        Product $productModel,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var _pageFactory */
        $this->_pageFactory = $pageFactory;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _productModel */
        $this->_productModel = $productModel;

         parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|ResultInterface|Page
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var  $searchParams */
        $searchParams = $this->getRequest()->getParams();
        $searchType = $this->getRequest()->getParam('search_type');
        $keywords = $this->getRequest()->getParam('keywords');

        /** if Search Type is Customers */
        if ($searchType === 'customers') {
            $customersList = $this->_customerFactory->create()
                ->renderCustomerSearchListing($searchParams);
            // phpcs:ignore
            echo $customersList;
        }

        /** if search type is Products */
        if ($searchType === 'products') {
            $productsList = $this->_productModel->renderProductSearchListing($searchParams);
            // phpcs:ignore
            echo $productsList;
        }

        // phpcs:disable
        exit;
        // return $this->_pageFactory->create();
        // phpcs:enable
    }
}

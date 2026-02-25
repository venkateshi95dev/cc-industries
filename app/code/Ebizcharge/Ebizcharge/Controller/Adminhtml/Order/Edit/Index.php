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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Order\Edit;

use Magento\Sales\Controller\Adminhtml\Order\Edit\Index as BaseIndex;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Escaper;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Order as EbizOrderModel;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfigModel;

/**
 * Index Controller
 */
class Index extends BaseIndex
{

    /**
     * Indicates how to process post data
     */
    private const ACTION_SAVE = 'save';
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;
    /**
     * @var EbizConfigModel
     */
    protected EbizConfigModel $ebizConfigModel;

    /**
     * @param Action\Context $context
     * @param ProductHelper $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param EbizConfigModel $ebizConfigModel
     */
    public function __construct(
        Action\Context                  $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper      $escaper,
        PageFactory                     $resultPageFactory,
        ForwardFactory                  $resultForwardFactory,
        EbizchargeLogger                $ebizchargeLogger,
        EbizConfigModel                 $ebizConfigModel

    )
    {
        parent::__construct($context, $productHelper, $escaper, $resultPageFactory, $resultForwardFactory);


        $productHelper->setSkipSaleableCheck(true);
        /** @var  escaper */
        $this->escaper = $escaper;
        /** @var  resultPageFactory */
        $this->resultPageFactory = $resultPageFactory;
        /** @var  resultForwardFactory */
        $this->resultForwardFactory = $resultForwardFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  ebizConfigModel */
        $this->ebizConfigModel = $ebizConfigModel;
    }


    /**
     * Index page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_initSession();
        $store = $this->ebizConfigModel->getStore();
        /** @var  $storeId */
        $storeId = $store->getStoreId() ?? "0";

        /** @var  $isOrderVoidSelectedNo */
        $isOrderVoidSelectedNo = $this->ebizConfigModel->getVoidOrderEditFlow($storeId);

        // if ($isOrderVoidSelectedNo && $isOrderVoidSelectedNo === "2") {
        //  $this->_getSession()->setQuoteId($this->_getSession()->getOrder()->getQuoteId());
        //  }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Order'));


        return $resultPage;
    }
}

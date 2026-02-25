<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AjaxCart\Controller\Index;

use Bss\AjaxCart\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Catalog\Controller\Product\View
{
    /**
     * Ajax cart helper :>.
     *
     * @var Data
     */
    protected $ajaxHelper;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Data $ajaxHelper
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Data $ajaxHelper
    ) {
        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
        $this->ajaxHelper = $ajaxHelper;
    }

    /**
     * Execute quick view.
     *
     * @return Forward|Redirect
     */
    public function execute()
    {
        if ($this->ajaxHelper->isEnabled()) {
            $resultPage = parent::execute();

            if ($resultPage instanceof \Magento\Framework\View\Result\Page) {
                $resultPage->getLayout()->getUpdate()->addHandle('catalog_product_view');
            }

            return $resultPage;
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('no-route');
            return $resultForward;
        }
    }
}

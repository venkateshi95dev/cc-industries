<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/10/2019
 */
namespace Crimson\Brand\Controller\Adminhtml\Brand;

use Crimson\Brand\Controller\Adminhtml\Brand;

class Index extends Brand
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Crimson_Brand::brand');
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Brands')));
        return $resultPage;
    }
}
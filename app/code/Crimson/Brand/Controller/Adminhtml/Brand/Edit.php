<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/11/2019
 */
namespace Crimson\Brand\Controller\Adminhtml\Brand;

use Crimson\Brand\Controller\Adminhtml\Brand;

class Edit extends Brand
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Crimson_Brand::edit';
    
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $brandId = $this->getRequest()->getParam('brand_id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Crimson_Brand::brand');

        if ($brandId === null) {
            $resultPage->getConfig()->getTitle()->prepend(__('New Brand'));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Brand'));
        }
        return $resultPage;
    }
}

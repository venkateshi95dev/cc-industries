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
 * @package    Bss_UrlRewriteImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\UrlRewriteImportExport\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\ImportExport\Controller\Adminhtml\Import\Index
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->messageManager->addNotice(
            $this->_objectManager->get('Magento\ImportExport\Helper\Data')->getMaxUploadSizeMessage()
        );
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Bss_UrlRewriteImportExport::importexport_url_rewrite_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import/Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import'));
        $resultPage->addBreadcrumb(__('Import'), __('Import'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_UrlRewriteImportExport::importexport_url_rewrite_import');
    }
}

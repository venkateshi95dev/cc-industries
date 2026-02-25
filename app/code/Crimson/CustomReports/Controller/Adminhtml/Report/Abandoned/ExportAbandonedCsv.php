<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Controller\Adminhtml\Report\Abandoned;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Reports\Controller\Adminhtml\Report\Shopcart\Abandoned as ShopCartAbandoned;

class ExportAbandonedCsv extends ShopCartAbandoned implements HttpGetActionInterface
{
    /**
     * Export abandoned carts by sku report grid to CSV format
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $fileName = 'shopcart_abandoned.csv';
        $content = $this->_view->getLayout()->createBlock(
            \Crimson\CustomReports\Block\Adminhtml\Report\Abandoned\Grid::class
        )->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}

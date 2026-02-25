<?php

namespace Crimson\Category\Controller\Adminhtml\Report;

use Crimson\Category\Service\GenerationCategoriesReport;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Controller\Adminhtml\Export\GridToCsv;
use Magento\Ui\Model\Export\ConvertToCsv;
use Psr\Log\LoggerInterface;

class Generate extends GridToCsv
{


    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        FileFactory $fileFactory,
        protected GenerationCategoriesReport $generationsCategoryReport,
        Filter $filter = null,
        LoggerInterface $logger = null
    )
    {
        parent::__construct($context, $converter, $fileFactory, $filter, $logger);
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $fileName   = $this->generationsCategoryReport->getReportFileName();

        return $this->fileFactory->create(
            $fileName,
            $this->generationsCategoryReport->generateCsvFile(),
            'var'
        );
    }
}

<?php

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Layout\Builder;

class ExportInboundCsv extends Action
{
    public const FILENAME = 'inbound-messegequeue.csv';

    /**
     * @var FileFactory
     */
    protected $_fileFactory;// phpcs:ignore

    /**
     * @param Context $context
     * @param FileFactory $FileFactory
     */
    public function __construct(Context $context, FileFactory $FileFactory)
    {
        parent::__construct($context);
        $this->_fileFactory = $FileFactory;
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $fileName = self::FILENAME;
        $content = $this->_view->getLayout()
            ->createBlock('I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueue\Grid')
            ->setIsExport(true)
            ->getCsv();

        return $this->_fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR
        );
    }
}

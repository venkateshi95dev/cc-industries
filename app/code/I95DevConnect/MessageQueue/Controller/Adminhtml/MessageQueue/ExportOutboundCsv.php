<?php

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportOutboundCsv extends Action
{
    /**
     * Default file name
     */
    public const FILENAME = 'outbound-messegequeue.csv';

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
            ->createBlock('I95DevConnect\MessageQueue\Block\Adminhtml\Outbound\Grid')
            ->setIsExport(true)
            ->setSaveParametersInSession(false)
            ->getCsv();

        return $this->_fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR
        );
    }
}

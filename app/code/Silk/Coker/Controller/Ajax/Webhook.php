<?php

namespace Silk\Coker\Controller\Ajax;

use Magento\Framework\App\Action\Context;

class Webhook extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;



    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        private readonly \Magento\Framework\Filesystem\DirectoryList $directoryList,
        private readonly \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }




    public function execute()
    {
        $var = $this->directoryList->getPath('var');
        $dir = $var."/payment";
        if (!is_dir($dir)) {
            $io = $this->file->mkdir($var.'/payment', 0775);
        }
        $inputJSON = file_get_contents('php://input');
        $headers = [];
        foreach (getallheaders() as $name => $value) {
            $headers[$name] = $value;
        }

        if (isset($headers['Client-Token'])) {
            $clientToken = $headers['Client-Token'];
            $dir = $var."/payment/";
            $logfile = $dir.$clientToken.".log";
            file_put_contents($logfile, $inputJSON . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}

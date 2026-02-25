<?php

namespace Silk\Coker\Controller\Ajax;

/**
 * Class Verify
 *
 * @package Silk\Jamboard\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        private readonly \Magento\Framework\Filesystem\DirectoryList $directoryList,
        private readonly \Magento\Checkout\Model\Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }




    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $clientToken = $this->getRequest()->getParam('clientToken');
        $this->logger->info('clientToken', [$clientToken]);

        $var = $this->directoryList->getPath('var');
        $dir = $var."/payment/";
        $filename = $dir.$clientToken.".log";
        $reponse = array("status"=>"error","url"=>"","message"=>"Please refresh the page and try again.");
        $this->logger->info('filename', [$filename]);
        if (file_exists($filename)) {
            $json_string = file_get_contents($filename);
            $data = json_decode($json_string, true);
            $token = $data["card"]["token"];
            $this->logger->info('data', [$data]);
            if (isset($token)) {
                $quote = $this->session->getQuote();
                if ($quote && $quote->getId()) {
                    $quote->setPaymenttoken($token);
                    $quote->setPaymentclienttoken($clientToken);
                    $quote->save();
                    $reponse = array("status"=>"success","url"=>"");
                } else {
                    $reponse = array("status"=>"error","url"=>"","message"=>"Please refresh the page and try again.");
                }
            } else {
                $reponse = array("status"=>"error","url"=>"","message"=>"Please refresh the page and try again.");
            }
        }
        $reponse = json_encode($reponse);
        echo $reponse;
    }
}

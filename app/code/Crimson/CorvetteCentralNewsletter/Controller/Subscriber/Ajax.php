<?php
namespace Crimson\CorvetteCentralNewsletter\Controller\Subscriber;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Newsletter\Controller\Subscriber\NewAction as CoreSubscribe;
use Magento\Framework\App\Action\Context;

class Ajax implements HttpPostActionInterface
{
    private $jsonFactory;
    private $coreAction;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CoreSubscribe $coreAction
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->coreAction  = $coreAction;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        try {
            // Let Magento validate & subscribe normally (throws exceptions on error)
            $response = $this->coreAction->execute();
            // If no exception, assume success
            $data = ['success' => true, 'message' => __('Thank you for your subscription.')];
        } catch (\Exception $e) {
            $data = ['success' => false, 'message' => $e->getMessage()];
        }
        return $resultJson->setData($data);
    }
}

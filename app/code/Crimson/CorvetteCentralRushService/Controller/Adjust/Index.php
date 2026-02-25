<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentralRushService\Controller\Adjust;

use Crimson\CorvetteCentralRushService\Service\RushService;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;

class Index extends Action
{

    public function __construct(
        Context $context,
        protected PageFactory $pageFactory,
        protected RequestInterface $request,
        protected RushService $rushService,
        protected Validator $formKeyValidator
    )
    {
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            if (!$this->formKeyValidator->validate($this->request)) {
                throw new \Exception('Invalid form key, please refresh the page and try again.');
            }

            $postData = $this->request->getPostValue();
            if (!$this->_isPostDataValid($postData)) {
                throw new \Exception('Invalid request');
            }

            $product = $this->rushService->doesSkuExist();
            if ($product === null) {
                throw new \Exception('Invalid request');
            }

            if (filter_var($postData['rush_value'], FILTER_VALIDATE_BOOLEAN) === true) {
                //checking to add the rush service product
                $response = $this->rushService->addRushServiceToCart($product);
            } else {
                //checking to remove the rush service product
                $response = $this->rushService->removeRushServiceFromCart();
            }

            $message = '';
            if (!empty($response['message']) && isset($response['error'])) {
                $message = $response['message'];
            }

            if ($message && $response['error'] === true) {
                $this->messageManager->addWarningMessage($message);
            } elseif ($message && $response['error'] === false) {
                $this->messageManager->addSuccessMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $this->handleRedirect();
    }

    private function handleRedirect()
    {
        $redirectPath = '';
        $refererUrl = $this->_redirect->getRefererUrl();
        if (str_contains($refererUrl, '/checkout/cart')) {
            $redirectPath = 'checkout/cart';
        } elseif (str_contains($refererUrl, '/checkout')) {
            $redirectPath = 'checkout';
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($redirectPath);
    }

    private function _isPostDataValid($postData): bool
    {
        return is_array($postData) && isset($postData['rush_value']);
    }
}

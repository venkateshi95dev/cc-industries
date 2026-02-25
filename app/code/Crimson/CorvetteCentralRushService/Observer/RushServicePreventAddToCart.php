<?php

namespace Crimson\CorvetteCentralRushService\Observer;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\CorvetteCentralRushService\Service\RushService;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class RushServicePreventAddToCart implements ObserverInterface
{

    public function __construct(
        protected StoreManagerInterface $storeManager,
        protected Session $checkoutSession,
        protected RushService $rushService,
        protected ResultFactory $resultFactory
    ) {}

    public function execute(Observer $observer)
    {
        if (!$this->_isCorvetteCentralCheck()) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        if ($product &&
            $product->getId() &&
            $this->rushService->isItemProductRushService($product) &&
            $this->rushService->doesCartHaveRushService($this->checkoutSession->getQuote())
        ) {
            throw new LocalizedException(__('Rush Service product is already in the cart.'));
        }

        return $this;
    }

    private function _isCorvetteCentralCheck(): bool
    {
        return (string)$this->storeManager->getWebsite()->getCode() === CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE &&
            $this->rushService->isEnabled();
    }

}

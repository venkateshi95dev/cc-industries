<?php

namespace Crimson\Customer\Service;

use Crimson\Customer\Model\CustomerConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CatalogRequestRedirect
{

    public function __construct(
        protected SessionManagerInterface $sessionManager,
        protected CustomerConfig          $customerConfig,
    ) {}

    public function checkCatalogRequest(RequestInterface $request): void
    {
        $isCatalogRequest = str_contains($request->getServer('HTTP_REFERER') ?? '', CustomerConfig::CATALOG_REQUEST_URL);
        if ($isCatalogRequest) {
            $this->setComingFromCatalogRequest();
        } else {
            $this->unsetComingFromCatalogRequest();
        }
    }

    public function setComingFromCatalogRequest(): void
    {
        $this->sessionManager->setData(CustomerConfig::CATALOG_REQUEST_FORM_INPUT_NAME, true);
    }

    public function isFromCatalogRequestSet()
    {
        return $this->sessionManager->hasData(CustomerConfig::CATALOG_REQUEST_FORM_INPUT_NAME);
    }

    public function unsetComingFromCatalogRequest(): void
    {
        $this->sessionManager->unsetData(CustomerConfig::CATALOG_REQUEST_FORM_INPUT_NAME);
    }

    public function isCatalogRequestRedirectEnabled(): bool
    {
        return $this->customerConfig->isCatalogRequestRedirectEnabled();
    }
}

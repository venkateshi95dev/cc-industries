<?php

namespace Crimson\MachCatalogRequest\Observer\ControllerActionPredispatchCatalogRequestFormPost;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ValidateRecaptcha implements ObserverInterface
{
    /** @var \Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface */
    private $isCaptchaEnabled;

    /** @var \Magento\ReCaptchaUi\Model\RequestHandlerInterface */
    private $captchaRequestHandler;

    /** @var \Magento\Framework\Url */
    private $url;

    public function __construct(
        \Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface $isCaptchaEnabled,
        \Magento\ReCaptchaUi\Model\RequestHandlerInterface $captchaRequestHandler,
        \Magento\Framework\Url $url
    ) {
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->captchaRequestHandler = $captchaRequestHandler;
        $this->url = $url;
    }

    public function execute(Observer $observer)
    {
        if (!$this->isCaptchaEnabled->isCaptchaEnabledFor('mach_catalog_request')) {
            return;
        }


        $controller = $observer->getControllerAction();
        $this->captchaRequestHandler->execute(
            'mach_catalog_request', 
            $controller->getRequest(),
            $controller->getResponse(),
            $this->url->getUrl('catalog/request/form')
        );
    }
}
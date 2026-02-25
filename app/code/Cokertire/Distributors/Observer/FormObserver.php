<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Cokertire\Distributors\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;

/**
 * FormObserver
 */
class FormObserver implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var IsCaptchaEnabledInterface
     */
    private $isCaptchaEnabled;

    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * @param UrlInterface $url
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     * @param RequestHandlerInterface $requestHandler
     */
    public function __construct(
        UrlInterface $url,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        RequestHandlerInterface $requestHandler
    ) {
        $this->url = $url;
        $this->isCaptchaEnabled = $isCaptchaEnabled;
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $key = 'distributors';
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/captcha.log');
$logger = new \Zend_Log();
$logger->addWriter($writer);

        if ($this->isCaptchaEnabled->isCaptchaEnabledFor($key)) {
            /** @var Action $controller */
            $controller = $observer->getControllerAction();
            $request = $controller->getRequest();
            $logger->info(print_r($request->getParams(), true));

            //$token = $request->getParam('g-recaptcha-response');
            $response = $controller->getResponse();
            $redirectOnFailureUrl = $this->url->getUrl('*/*/index');

            $this->requestHandler->execute($key, $request, $response, $redirectOnFailureUrl);
        }
    }
}

<?php

declare(strict_types=1);

namespace Crimson\Sales\Plugin;

use Amasty\Customform\Controller\Form\Submit;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\ReCaptchaUi\Model\RequestHandlerInterface;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;

class SubmitPlugin
{
    const RECAPTCHA_FORM_ID = "start_a_return_form";

    public function __construct(
        protected ValidatorInterface $validator,
        protected ScopeConfigInterface $scopeConfig,
        protected RequestHandlerInterface $captchaRequestHandler,
        protected UrlInterface $url
    ) {
    }

    /**
     * @param Submit $subject
     * @param callable $proceed
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function aroundExecute(Submit $subject, callable $proceed) : ResultInterface
    {
        $isEnabled = (bool) $this->scopeConfig->getValue(
            'recaptcha_frontend/type_for/start_a_return_form',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $recaptchaFormId = $subject->getRequest()->getParam('recaptcha_form_id');

        if (!$isEnabled || $recaptchaFormId !== self::RECAPTCHA_FORM_ID) {
            return $proceed();
        }

        $this->captchaRequestHandler->execute(
            self::RECAPTCHA_FORM_ID,
            $subject->getRequest(),
            $subject->getResponse(),
            $this->url->getUrl('start-a-return')
        );

        return $proceed();
    }
}

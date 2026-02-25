<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentralStyleGuide\Controller\Styleguide;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Crimson\CorvetteCentralStyleGuide\Model\Config;
use Magento\Framework\Controller\ResultInterface;

class Index implements ActionInterface, HttpGetActionInterface
{

    public function __construct(
        private readonly PageFactory $pageResultFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly Config $config
    ) {
    }

    public function execute(): ResultInterface
    {
        if (!$this->config->isEnabled()) {
            $resultRedirect = $this->redirectFactory->create();
            return $resultRedirect->setPath('/');
        }

        $pageResult = $this->pageResultFactory->create();
        $pageResult->getConfig()->getTitle()->set(__('Theme Elements Style Guide'));
        return $pageResult;
    }
}

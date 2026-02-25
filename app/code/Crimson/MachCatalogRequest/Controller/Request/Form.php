<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 1:26 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Controller\Request;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Form
 * @package Crimson\MachCatalogRequest\Controller\Request
 */
class Form extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context      $context
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Order view form page
     *
     * @return Redirect|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Catalog Request Form'));
        $this->getBreadcrumbs($resultPage);
        return $resultPage;
    }

    /**
     * Get Breadcrumbs for current controller action
     *
     * @param Page $resultPage
     *
     * @return void
     * @throws NoSuchEntityException
     */
    protected function getBreadcrumbs(Page $resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        if (!$breadcrumbs) {
            return;
        }
        $breadcrumbs->addCrumb(
            'home',
            array(
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->storeManager->getStore()->getBaseUrl()
            )
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            ['label' => __('Request a Catalog'), 'title' => __('Request a Catalog')]
        );
    }
}

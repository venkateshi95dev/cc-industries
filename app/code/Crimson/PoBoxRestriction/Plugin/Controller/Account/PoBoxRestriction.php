<?php
/**
 * @namespace   Crimson
 * @module      PoBoxRestriction
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        1/23/2019
 */
namespace Crimson\PoBoxRestriction\Plugin\Controller\Account;

use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\Message\ManagerInterface;
use Crimson\PoBoxRestriction\Helper\Data as Helper;

/**
 * Class PoBoxRestriction
 * @package Crimson\PoBoxRestriction\Plugin\Controller\Account
 */
class PoBoxRestriction
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlModel;

    /**
     * @var RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * PoBoxRestriction constructor.
     * @param UrlFactory $urlFactory
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        UrlFactory $urlFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        Helper $helper
    )
    {
        $this->_urlModel = $urlFactory->create();
        $this->_resultRedirectFactory = $redirectFactory;
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
    }

    /**
     * @param CreatePost $subject
     * @param \Closure $proceed
     * @return Redirect|mixed
     */
    public function aroundExecute(CreatePost $subject, \Closure $proceed)
    {
        if ($this->_helper->getModuleConfig()) {

            if (!$subject->getRequest()->getPost('create_address')) {
                return $proceed();
            }

            $data = $subject->getRequest()->getParams();
            $isPoBox = false;
            foreach ($data['street'] as $line) {
                $isPoBox = $this->_helper->isPoStreet($line);
                if ($isPoBox) {
                    break;
                }
            }

            if ($isPoBox) {
                $this->_messageManager->addErrorMessage(
                    'We don\'t ship to PO Boxes.'
                );
                $defaultUrl = $this->_urlModel->getUrl('*/*/create', ['_secure' => true]);
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->_resultRedirectFactory->create();
                return $resultRedirect->setUrl($defaultUrl);
            }

            return $proceed();
        }

        return $proceed();
    }
}

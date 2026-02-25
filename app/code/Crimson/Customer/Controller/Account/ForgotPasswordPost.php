<?php
/**
 * @namespace   Crimson
 * @module      Customer
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        12/28/2018
 * @brief
 */
namespace Crimson\Customer\Controller\Account;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Store\Model\StoreManagerInterface;

/**
 * ForgotPasswordPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost implements HttpPostActionInterface
{
    /**
     * @var Customer
     */
    protected $_customerCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     * @param StoreManagerInterface $storeManager
     * @param Customer $customerCollection
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        StoreManagerInterface $storeManager,
        Customer $customerCollection
    ) {
        $this->_storeManager = $storeManager;
        $this->_customerCollection = $customerCollection;
        parent::__construct($context, $customerSession, $customerAccountManagement, $escaper);
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {
            if (!ValidatorChain::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customer = $this->_customerCollection->setWebsiteId($websiteId)->loadByEmail($email);
            $customerId = $customer->getId();
            if ($customerId) {
                try {
                    $this->customerAccountManagement->initiatePasswordReset(
                        $email,
                        AccountManagement::EMAIL_RESET
                    );
                } catch (NoSuchEntityException $exception) {
                    // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
                } catch (SecurityViolationException $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                    return $resultRedirect->setPath('*/*/forgotpassword');
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('We\'re unable to send the password reset email.')
                    );
                    return $resultRedirect->setPath('*/*/forgotpassword');
                }
                $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            } else {
                $this->messageManager->addErrorMessage($this->getErrorMessage());
            }
            return $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }

    /**
     * @param string $email
     * @return Phrase
     */
    protected function getSuccessMessage($email): Phrase
    {
        return __(
            'You will receive an email with instructions on how to proceed with resetting your password soon.',
            $this->escaper->escapeHtml($email)
        );
    }

    /**
     * @return Phrase
     */
    protected function getErrorMessage(): Phrase
    {
        return __(
            'The email address you entered is not associated with an account.'
        );
    }
}

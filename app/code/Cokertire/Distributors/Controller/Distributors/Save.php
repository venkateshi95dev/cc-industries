<?php

namespace Cokertire\Distributors\Controller\Distributors;

use Cokertire\Distributors\Model\RequestFactory;
use Cokertire\Distributors\Model\ResourceModel\Request\Collection;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Save extends Action implements HttpPostActionInterface
{
    protected $_resultPageFactory;

    protected $_distributorsRequestFactory;

    protected $_distributorsRequestCollectionFactory;

    protected $resultRedirectFactory;

    protected ScopeConfigInterface $scopeConfig;

    const EMAIL = 'distributors/general/email';
    const IP    = 'distributors/general/ip';

    public function __construct(Context $context,
     RequestFactory $requestFactory,
     Collection $_RequestCollectionFactory,
     ScopeConfigInterface $scopeInterface,
     RedirectFactory $resultRedirectFactory,
     PageFactory $resultPageFactory
     ) {

        $this->_distributorsRequestFactory           = $requestFactory;
        $this->_distributorsRequestCollectionFactory = $_RequestCollectionFactory;
        $this->_resultPageFactory                    = $resultPageFactory;
        $this->scopeConfig                           = $scopeInterface;
        $this->resultRedirectFactory                 = $resultRedirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $name      = $this->getRequest()->getParam('name');
        $email     = $this->getRequest()->getParam('email');
        $telephone = $this->getRequest()->getParam('telephone');
        $country   = $this->getRequest()->getParam('country');
        $business  = $this->getRequest()->getParam('business');
        $comments  = $this->getRequest()->getParam('comments');
        $unique_id = $this->getRequest()->getParam('unique_id');

        if((trim($name) != '') && (trim($email) != '') && trim($comments) != '') {
            $collection = $this->_distributorsRequestCollectionFactory->addFieldToFilter("unique_id",$unique_id)->getFirstItem();
            $check_for_match = $collection->getEmail();
            if($email == $check_for_match) {
                $this->messageManager->addErrorMessage(
                    __('Please wait 1 minute before submitting another inquiry.')
                );
            } else {
                $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
                if (!preg_match($pattern, $email)) {
                    $this->messageManager->addErrorMessage(
                        __('Please correct your email address and try submitting again.')
                    );
                } else {
                    $requests = $this->_initDistributors();
                    $arr = array("name"=>$name,"email"=>$email,"telephone"=>$telephone,"country"=>$country,"business"=>$business,"comments"=>$comments,"unique_id"=>$unique_id);
                    $requests->setData($arr);
                    $requests->save();

                    $body = 'Name: ' . $name . '<br>
                         Email: ' . $email . '<br>
                         Phone: ' . $telephone . '<br>
                         Country: ' . $country . '<br>
                         Business: ' . $business . '<br><br>
                         Comment/Question: ' . $comments . '</br>';
                    try {
                        $mail = new \Zend_Mail();
                        $storeScope = ScopeInterface::SCOPE_STORE;
                        $mail->setFrom($email, $name);
                        $Toemail = $this->scopeConfig->getValue(self::EMAIL, $storeScope);
                        $mail->addTo($Toemail, 'Coker Tire');
                        $mail->setSubject('International Distributor Information Request');
                        $mail->setBodyHtml($body);
                        $mail->send();

                    } catch(\Exception $e) {
                        file_put_contents(BP . "/var/log/email.txt",$e->getMessage());
                    }

                    $this->messageManager->addSuccessMessage(
                        __('We will follow-up with you as soon as possible. Thank you.')
                    );
                }
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('You must fill out the required fields before submitting.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath('distributors/distributors');
    }

    public function getErrorMessage($error, $regenerate): array
    {
        return [
            "error" => $error,
            "regenerate" => $regenerate
        ];
    }

    public function getSuccessMessage($success, $regenerate): array
    {
        return [
            "success" => $success,
            "regenerate" => $regenerate
        ];
    }

    protected function _initDistributors()
    {
       return $this->_distributorsRequestFactory->create();
    }
}

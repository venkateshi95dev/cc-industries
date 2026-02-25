<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 3:11 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Controller\Request;

use Crimson\MachCatalogRequest\Api\CatalogRequestRepositoryInterface;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterface;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterfaceFactory;
use Crimson\MachCatalogRequest\Model\Source\AvailableCatalogs;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Phrase;
use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormPost extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var AvailableCatalogs
     */
    protected $availableCatalogs;
    /**
     * @var CatalogRequestInterfaceFactory
     */
    protected $catalogRequestFactory;
    /**
     * @var CatalogRequestRepositoryInterface
     */
    protected $catalogRequestRepository;
    /**
     * @var RegionFactory
     */
    protected $regionFactory;
    /**
     * @var Session
     */
    protected $session;

	/**
	 * @var Subscriber
	 */
	protected $subscriber;

	/**
	 * @var EmailValidator
	 */
	private $emailValidator;

    public function __construct(
        Context $context,
        Session $customerSession,
        CatalogRequestInterfaceFactory $catalogRequestFactory,
        CatalogRequestRepositoryInterface $catalogRequestRepository,
        AvailableCatalogs $availableCatalogs,
        Subscriber $subscriber,
        RegionFactory $regionFactory,
        EmailValidator $emailValidator = null
    ) {
        $this->session = $customerSession;
        parent::__construct($context);
        $this->catalogRequestFactory    = $catalogRequestFactory;
        $this->catalogRequestRepository = $catalogRequestRepository;
        $this->availableCatalogs        = $availableCatalogs;
        $this->regionFactory            = $regionFactory;
        $this->subscriber            = $subscriber;
	    $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailValidator::class);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {

                $this->getRequest()->setPostValue('country', $this->getRequest()->getParam('country_id', null));
                $this->getRequest()->setPostValue('zip', $this->getRequest()->getParam('postcode', null));

                $this->session->setFormData($this->getRequest()->getPostValue());

                $errors = $this->_validateRequiredFields();
                if (count($errors) > 0) {
                    $inputException = new InputException();
                    foreach ($errors as $error) {
                        $inputException->addError($error);
                    }

                    throw $inputException;
                }

                $catalogs      = $this->_request->getParam('catalog');
                $validCatalogs = $this->_filterValidCatalogs($catalogs);
                if (empty($validCatalogs)) {
                    throw new InputException(__('You must select a catalog.'));
                }

	            $subscription    = $this->_request->getParam('subscribe', 0);
                $email = $this->_request->getParam(CatalogRequestInterface::EMAIL, null);
                foreach ($validCatalogs as $validCatalog) {
                    $catalogRequest = $this->catalogRequestFactory->create();
                    $catalogRequest->setRequestedItems($validCatalog);

                    foreach ($this->_getSimpleFields() as $field => $label) {
                        $data   = $this->_request->getParam($field);
                        $method = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($field);
                        if (method_exists($catalogRequest, $method)) {
                            $catalogRequest->$method($data);
                        }
                    }

                    //set region
                    $state = $this->_getState();
                    $catalogRequest->setState($state);

                    //set street
                    $street = $this->_request->getParam('street');
                    if ($street[0] ?? false) {
                        $catalogRequest->setStreet1($street[0]);
                    }
                    if ($street[1] ?? false) {
                        $catalogRequest->setStreet2($street[1]);
                    }

                    $this->catalogRequestRepository->save($catalogRequest);
                }

                $this->session->setFormData(null);

                if ($subscription && $email) {
					$this->subscriber->subscribe($email);
                }

                return $this->_redirect('catalog-request-success.html');
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addErrorMessage($error->getMessage());
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error occurred, please try your request again later.'));
            }
        }

        return $this->_redirect('catalog/request/form');
    }

    /**
     * @return Phrase[]
     */
    protected function _validateRequiredFields(): array
    {
        $simpleFields = $this->_getSimpleFields();

        $errors = [];
        foreach ($simpleFields as $field => $label) {
            $value = $this->_request->getParam($field);
            if (!$value) {
                $errors[] = __('%1 is a required field.', $label);
            }
        }

        //validate street.
        $street = $this->_request->getParam('street');
        if (!$street) {
            $errors[] = __('Street is a required field.');
        } else {
            $street1 = reset($street);
            if (!$street1) {
                $errors[] = __('Street is a required field.');
            }
        }

        //validate region
        $region   = $this->_request->getParam('region');
        $regionId = $this->_request->getParam('region_id');
        if (!$region && !$regionId) {
            $errors[] = __('Region is a required field.');
        }

        //validate Email
	    $eMail = $this->_request->getParam(CatalogRequestInterface::EMAIL);
	    if (!$this->emailValidator->isValid($eMail)) {
		    $errors[] = __('Please enter a valid email address.');
	    }

        return $errors;
    }

    /**
     * @return array
     */
    protected function _getSimpleFields(): array
    {
        return [
            CatalogRequestInterface::NAME    => 'Name',
            CatalogRequestInterface::EMAIL   => 'Email',
            CatalogRequestInterface::CITY    => 'City',
            CatalogRequestInterface::ZIP     => 'Zip',
            CatalogRequestInterface::COUNTRY => 'Country',
        ];
    }

    /**
     * @param string[] $catalogs
     *
     * @return array
     */
    protected function _filterValidCatalogs($catalogs): array
    {
        if (!$catalogs) {
            return [];
        }

        $availableCatalogs = $this->availableCatalogs->toOptionHash();

        $validCatalogs = [];
        foreach ($catalogs as $catalog) {
            if (isset($availableCatalogs[$catalog])) {
                $validCatalogs[] = $catalog;
            }
        }

        return $validCatalogs;
    }

    /**
     * @return mixed|string
     * @throws InputException
     */
    protected function _getState()
    {
        $regionId = $this->_request->getParam('region_id');

        if ($regionId) {
            $state = $this->_getRegionCode($regionId);
            if ($state) {
                return $state;
            } else {
                throw InputException::invalidFieldValue('state', $regionId);
            }
        }

        return $this->_request->getParam('region');
    }

    /**
     * @param $regionId
     * @return string
     */
    protected function _getRegionCode($regionId): string
    {
        $region = $this->regionFactory->create()->load($regionId);

        return $region->getCode();
    }
}

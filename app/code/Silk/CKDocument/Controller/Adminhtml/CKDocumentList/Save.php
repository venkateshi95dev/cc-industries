<?php
namespace Silk\CKDocument\Controller\Adminhtml\CKDocumentList;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Silk\CKDocument\Model\CKDocument;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Save CMS document action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Silk\CKDocument\Controller\Adminhtml\CKDocumentList implements HttpPostActionInterface
{

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Silk\CKDocument\Model\CKDocumentFactory
     */
    private $documentFactory;

    /**
     * @var \Silk\CKDocument\Api\CKDocumentRepositoryInterface
     */
    private $documentRepository;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param \Silk\CKDocument\Model\CKDocumentFactory|null $documentFactory
     * @param \Silk\CKDocument\Api\CKDocumentRepositoryInterface|null $documentRepository
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Silk\CKDocument\Model\CKDocumentFactory $documentFactory = null,
        \Silk\CKDocument\Api\CKDocumentRepositoryInterface $documentRepository = null
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->authSession = $authSession;
        $this->documentFactory = $documentFactory ?: ObjectManager::getInstance()->get(\Silk\CKDocument\Model\CKDocumentFactory::class);
        $this->documentRepository = $documentRepository
            ?: ObjectManager::getInstance()->get(\Silk\CKDocument\Api\CKDocumentRepositoryInterface::class);
        parent::__construct($context,$coreRegistry);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = CKDocument::STATUS_ENABLED;
            }
            if (empty($data['document_id'])) {
                $data['document_id'] = null;
            }
            /** @var \Silk\CKDocument\Model\CKDocument $model */
            $model = $this->documentFactory->create();

            $id = $this->getRequest()->getParam('document_id');
            if ($id) {
                try {
                    $model = $this->documentRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This document no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            //\Magento\User\Model\User|null
            $currentUser = $this->authSession->getUser();
            if($currentUser){
                $data['modified_by'] = $currentUser->getName();
                if(!$id){
                    $data['created_by'] = $currentUser->getName();
                }
            }
            $model->setData($data);

            try {
                $this->_eventManager->dispatch(
                    'cms_document_prepare_save',
                    ['document' => $model, 'request' => $this->getRequest()]
                );

                $this->documentRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the document.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Throwable $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the document.'));
            }

            $this->dataPersistor->set('cms_document', $data);
            return $resultRedirect->setPath('*/*/edit', ['document_id' => $this->getRequest()->getParam('document_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process result redirect
     *
     * @param \Silk\CKDocument\Api\Data\CKDocumentInterface $model
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newCKDocument = $this->documentFactory->create(['data' => $data]);
            $newCKDocument->setId(null);
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newCKDocument->setIdentifier($identifier);
            $newCKDocument->setIsActive(false);
            $this->documentRepository->save($newCKDocument);
            $this->messageManager->addSuccessMessage(__('You duplicated the document.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'document_id' => $newCKDocument->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('cms_document');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['document_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

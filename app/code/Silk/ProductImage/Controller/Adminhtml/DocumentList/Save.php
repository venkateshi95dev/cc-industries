<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\DocumentList;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Silk\ProductImage\Model\Document;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
/**
 * Save CMS document action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Silk\ProductImage\Controller\Adminhtml\DocumentList implements HttpPostActionInterface
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Json
     */
    private $jsonManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Silk\ProductImage\Model\DocumentFactory
     */
    private $documentFactory;

    /**
     * @var \Silk\ProductImage\Api\DocumentRepositoryInterface
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
     * @param \Silk\ProductImage\Model\DocumentFactory|null $documentFactory
     * @param \Silk\ProductImage\Api\DocumentRepositoryInterface|null $documentRepository
     */
    public function __construct(
        Action\Context $context,
        Json $jsonManager,
        Session $session,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Silk\ProductImage\Model\DocumentFactory $documentFactory = null,
        \Silk\ProductImage\Api\DocumentRepositoryInterface $documentRepository = null,
        LoggerInterface $logger
    ) {
        $this->jsonManager = $jsonManager;
        $this->session = $session;
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->authSession = $authSession;
        $this->documentFactory = $documentFactory ?: ObjectManager::getInstance()->get(\Silk\ProductImage\Model\DocumentFactory::class);
        $this->documentRepository = $documentRepository
            ?: ObjectManager::getInstance()->get(\Silk\ProductImage\Api\DocumentRepositoryInterface::class);
        $this->logger = $logger;
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

        $productPositions = $this->getRequest()->getParam('vm_document_products');

        if ($productPositions) {
            try {
                $productPositions = $this->jsonManager->unserialize($productPositions);
                $this->session->setDocumentProductPositions($productPositions);
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning($e->getMessage());
            }
        }
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Document::STATUS_ENABLED;
            }
            if (empty($data['document_id'])) {
                $data['document_id'] = null;
            }
            /** @var \Silk\ProductImage\Model\Document $model */
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
                $this->documentRepository->save($model);
                $this->_eventManager->dispatch(
                    'img_document_prepare_save',
                    ['document' => $model, 'request' => $this->getRequest()]
                );
                $this->messageManager->addSuccessMessage(__('You saved the document.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
                $this->logger->critical($e);
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the document.'));
                $this->logger->critical($e);
            }

            $this->dataPersistor->set('img_document', $data);
            return $resultRedirect->setPath('*/*/edit', ['document_id' => $this->getRequest()->getParam('document_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process result redirect
     *
     * @param \Silk\ProductImage\Api\Data\DocumentInterface $model
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newDocument = $this->documentFactory->create(['data' => $data]);
            $newDocument->setId(null);
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newDocument->setIdentifier($identifier);
            $newDocument->setIsActive(false);
            $this->documentRepository->save($newDocument);
            $this->messageManager->addSuccessMessage(__('You duplicated the document.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'document_id' => $newDocument->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('img_document');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['document_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

<?php
namespace Silk\CKDocument\Block\Adminhtml\CKDocument\Edit;

use Magento\Backend\Block\Widget\Context;
use Silk\CKDocument\Api\CKDocumentRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var CKDocumentRepositoryInterface
     */
    protected $documentRepository;

    /**
     * @param Context $context
     * @param CKDocumentRepositoryInterface $documentRepository
     */
    public function __construct(
        Context $context,
        CKDocumentRepositoryInterface $documentRepository
    ) {
        $this->context = $context;
        $this->documentRepository = $documentRepository;
    }

    /**
     * Return CMS document ID
     *
     * @return int|null
     */
    public function getCKDocumentId()
    {
        try {
            return $this->documentRepository->getById(
                $this->context->getRequest()->getParam('document_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

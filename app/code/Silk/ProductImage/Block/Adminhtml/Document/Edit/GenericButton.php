<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Block\Adminhtml\Document\Edit;

use Magento\Backend\Block\Widget\Context;
use Silk\ProductImage\Api\DocumentRepositoryInterface;
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
     * @var DocumentRepositoryInterface
     */
    protected $documentRepository;

    /**
     * @param Context $context
     * @param DocumentRepositoryInterface $documentRepository
     */
    public function __construct(
        Context $context,
        DocumentRepositoryInterface $documentRepository
    ) {
        $this->context = $context;
        $this->documentRepository = $documentRepository;
    }

    /**
     * Return CMS document ID
     *
     * @return int|null
     */
    public function getDocumentId()
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

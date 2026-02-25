<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/15/2019
 */
namespace Crimson\Brand\Block\Adminhtml\Brand\Edit\Buttons;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Crimson\Brand\Api\BrandRepositoryInterface;

class Generic
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var BrandRepositoryInterface
     */
    protected $brandRepository;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository
    ) {
        $this->context = $context;
        $this->brandRepository = $brandRepository;
        $this->authorization = $context->getAuthorization();
    }

    /**
     * @return int|null
     * @throws LocalizedException
     */
    public function getBrandId(): ?int
    {
        try {
            return $this->brandRepository->getById(
                $this->context->getRequest()->getParam('brand_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

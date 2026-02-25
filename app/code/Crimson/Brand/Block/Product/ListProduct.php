<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/18/2019
 */
namespace Crimson\Brand\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data;
use Crimson\Brand\Api\BrandRepositoryInterface;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Crimson\Brand\Api\BrandRepositoryInterface
     */
    protected $_brandRepository;


    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        BrandRepositoryInterface $brandRepository,
        array $data = []
    ) {
        $this->_brandRepository = $brandRepository;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function getBrand($brandId)
    {
        return $this->_brandRepository->getById($brandId);
    }
}
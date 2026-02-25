<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        06/15/2019
 */
namespace Crimson\Brand\Helper;

use Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Crimson\Brand\Api\BrandRepositoryInterface;

/**
 * Class Data
 * @package Crimson\Brand\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $_brandCollectionFactory;

	/**
	 * @var BrandRepositoryInterface
	 */
	protected $_brandRepository;

	/**
	 * Data constructor.
	 * @param Context $context
	 * @param BrandRepositoryInterface $brandRepository
	 * @param CollectionFactory $brandCollectionFactory
	 */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        CollectionFactory $brandCollectionFactory
    ) {
        $this->_brandCollectionFactory = $brandCollectionFactory;
	    $this->_brandRepository = $brandRepository;
        parent::__construct($context);
    }

    /**
     * Get Brand Url Key by Brand Name
     * @param $brandName
     * @return mixed
     */
    public function getBrandUrlKeyByName($brandName)
    {
        $collection = $this->_brandCollectionFactory->create();
        $collection->addFieldToFilter('name', ['eq' => $brandName]);
        $brand = $collection->getFirstItem();
        return $brand->getUrlKey();
    }

	/**
	 * @param $brandId
	 * @return bool
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getDisplayBrandInfo($brandId): bool
	{
		$brand = $this->_brandRepository->getById($brandId);
		if ($brand && !$brand->getDisplay()) {
			return false;
		}

		return true;
	}
}

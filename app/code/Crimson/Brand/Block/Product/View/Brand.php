<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/18/2019
 */
namespace Crimson\Brand\Block\Product\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Crimson\Brand\Api\BrandRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Brand extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var BrandRepositoryInterface
     */
    protected $_brandRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Brand constructor.
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        BrandRepositoryInterface $brandRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data
    ) {
        $this->registry = $registry;
        $this->_brandRepository = $brandRepository;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

	/**
	 * @return Product|mixed
	 * @throws LocalizedException
	 */
    public function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');
            if (!$this->product->getId()) {
                throw new LocalizedException(__('Failed to initialize product'));
            }
        }
        return $this->product;
    }

    /**
     * @param $brandId
     * @return \Crimson\Brand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrand($brandId)
    {
        return $this->_brandRepository->getById($brandId);
    }

    public function getBrandImageUrl()
    {
        return $mediaUrl = $this ->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ). 'brand/images/image/';
    }

	/**
	 * @return bool|\Crimson\Brand\Api\Data\BrandInterface
	 * @throws LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getDisplayBrandInfo()
	{
		$_product = $this->getProduct();
		$brandId = $_product->getData('brands');
		if ($brandId) {
			$brand = $this->getBrand($brandId);
			if ($brand && $brand->getDisplay()) {
				return $brand;
			}
		}

		return false;
	}

}
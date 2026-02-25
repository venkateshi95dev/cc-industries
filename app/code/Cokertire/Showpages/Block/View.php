<?php
namespace Cokertire\Showpages\Block;

class View extends \Magento\Framework\View\Element\Template
{
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	public $_storeManager;

	private $productFactory;

	protected $_image;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Helper\Image $image,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Framework\Registry $coreRegistry
	)
	{
		parent::__construct($context);
		$this->_coreRegistry  = $coreRegistry;
		$this->_storeManager = $storeManager;
		$this->_image = $image;
		$this->productFactory = $productFactory;
	}

	public function getShowpages()
	{
	    $showpages = $this->_coreRegistry->registry('current_showpages');
	    return $showpages;
	}

	public function getMediaUrl(){
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

	public function loadMyProduct($sku)
	{
	    $product = $this->productFactory->create();
	     $product = $product->loadByAttribute('sku', $sku);
	    if($product && $product->getId()){
	    	return $product;
	    }
	    return null;
	}

	public function getBaseUrl(){
		return $this->_storeManager->getStore()->getBaseUrl();
	}

	public function getProductImageUrl($product)
	{
        return $this->_image->init($product, 'product_base_image')->constrainOnly(FALSE)
                    ->keepAspectRatio(TRUE)
                    ->keepFrame(FALSE)
                    ->resize(400)
                    ->getUrl();
	}

}

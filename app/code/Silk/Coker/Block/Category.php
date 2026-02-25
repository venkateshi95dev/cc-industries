<?php
namespace Silk\Coker\Block;

use Magento\Framework\Registry;
use Magento\Catalog\Api\CategoryRepositoryInterface;
class Category extends \Magento\Framework\View\Element\Template
{

	private $registry;

	private $_categoryFactory;

	protected $_storeManager;
	protected $categoryRepository;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		CategoryRepositoryInterface $categoryRepository,
		Registry $registry,
		\Magento\Catalog\Model\CategoryFactory  $categoryFactory,

		\Magento\Framework\View\Element\Template\Context $context
		)
	{
		$this->_storeManager = $storeManager;
		$this->categoryRepository = $categoryRepository;
		$this->_categoryFactory = $categoryFactory;
		$this->registry = $registry;
		parent::__construct($context);
	}


	public function getCurrentCategory(){
		return $category = $this->registry->registry('current_category');
	}

	public function getCurrentChildCategory(){
		$category = $this->getCurrentCategory();
		if($category && $category->getId()){
			$collection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect('*')
	              ->addAttributeToFilter('is_active', 1)
	              ->setOrder('position', 'ASC')
	              ->addIdFilter($category->getChildren());
	      	return $collection;
		}
		return null;
	}
	public  function getCategory($categoryId)
	{
	    $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
	    return $category->getUrl();
	}

}
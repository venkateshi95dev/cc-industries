<?php
namespace Cokertire\Distributors\Block;


use Cokertire\Distributors\Model\Distributors as distributorsFactory;
 
class Distributors extends \Magento\Framework\View\Element\Template
{

	protected $_distributorsCollectionFactory;
	protected $directoryBlock;
	protected $_countryCollectionFactory;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
		\Cokertire\Distributors\Model\ResourceModel\Distributors\Collection $_distributorsCollectionFactory,
		\Magento\Directory\Block\Data $directoryBlock
		)
	{
		$this->_distributorsCollectionFactory = $_distributorsCollectionFactory;
		$this->directoryBlock = $directoryBlock;
		$this->_countryCollectionFactory = $countryCollectionFactory;
		parent::__construct($context);
	}

	public function getDistributors(){
		$collection = $this->_distributorsCollectionFactory->addFieldToFilter("status","1");
		return $collection;
	}

	public function getCountryCollection(){
		$collection = $this->_countryCollectionFactory->create()->loadByStore();
		return $collection;
	}


	public function getCountries(){
		return $options = $this->getCountryCollection()->toOptionArray();
	}

	public function getFormAction() {
	    return $this->getUrl('distributors/distributors/save', ['_secure' => true]);
	}


}
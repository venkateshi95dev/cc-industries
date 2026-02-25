<?php
namespace Cokertire\Disclaimers\Block;
 
class Disclaimers extends \Magento\Framework\View\Element\Template
{

	protected $_disclaimersCollectionFactory;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Cokertire\Disclaimers\Model\ResourceModel\Disclaimers\Collection $disclaimersCollectionFactory
		)
	{
		$this->_disclaimersCollectionFactory = $disclaimersCollectionFactory;
		parent::__construct($context);
	}

	public function cleanupDisclaimer($disclaimer)
	{
	    $disclaimers = [];

	    if(!$disclaimer)
	        return;

	    foreach($disclaimer as $disclaim){

	        if(!in_array($disclaim, $disclaimers))
	            $disclaimers[] = $disclaim;
	    }

	    return $disclaimers;
	}

	public function getDisclaimers(){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Framework\Registry')->registry('current_product');
		if(!$product){
			return null;
		}
		$attributeSetId = $product->getAttributeSetId();
		$collection = $this->_disclaimersCollectionFactory
		->addFieldToFilter('attribute_set', array('eq' => $attributeSetId))
		->addFieldToFilter("status","1");

		return $this->cleanupDisclaimer($collection);
	}

}
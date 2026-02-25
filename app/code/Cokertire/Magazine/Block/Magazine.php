<?php
namespace Cokertire\Magazine\Block;

class Magazine extends \Magento\Framework\View\Element\Template
{

	protected $_magazineCollectionFactory;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Cokertire\Magazine\Model\ResourceModel\Magazine\Collection $magazineCollectionFactory
		)
	{
		$this->_magazineCollectionFactory = $magazineCollectionFactory;
		parent::__construct($context);
	}

	public function getMagazine()
	{
		$year = $this->getRequest()->getParam('year')?:date('Y');
	    $magazine = $this->_magazineCollectionFactory
			->addFieldToFilter('status', array('eq' => '1'))
			->addFieldToFilter('year', array('eq'=>$year))
			->getFirstItem();

	    return $magazine;
	}



}

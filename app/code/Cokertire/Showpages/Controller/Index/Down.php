<?php
namespace Cokertire\Showpages\Controller\Index;

use Magento\Framework\App\Action\Context;
use Cokertire\Showpages\Model\Invite;
use \Magento\Framework\Stdlib\DateTime as DateTime;

class Down extends \Magento\Framework\App\Action\Action
{

	protected $_resultPageFactory;

	/**
	 * Showpages Factory
	 *
	 * @var \Cokertire\Showpages\Model\ShowpagesFactory
	 */
	protected $_ShowpagesFactory;



	public function __construct(
	Context $context,
	\Cokertire\Showpages\Model\ShowpagesFactory $showpagesFactory,
	\Magento\Framework\View\Result\PageFactory $resultPageFactory
	 )
	{
		$this->_ShowpagesFactory           = $showpagesFactory;
	    $this->_resultPageFactory = $resultPageFactory;
	    parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->_resultPageFactory->create();
		$showpagesId  = (int) $this->getRequest()->getParam('id');
		$showpages    = $this->_ShowpagesFactory->create();
		if ($showpagesId) {
		    $showpages = $showpages->load($showpagesId);
		}else{
			/** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('showpages');
			return $resultRedirect;
		}
		$show_date = $showpages->getShowDate();
		$show_date_end = $showpages->getShowDateEnd();
		if(strpos($show_date?:'',"-")){
		    $show_date = strtotime($show_date);
		    $show_date  = date("d/m/Y",$show_date);
		}
		if(strpos($show_date_end?:'',"-")){
		    $show_date_end = strtotime($show_date_end);
		    $show_date_end  = date("d/m/Y",$show_date_end);
		}
		$start_date_array = explode('/',$show_date?:'');
		$end_date_array = explode('/',$show_date?:'');

		$start_date = new \DateTime();
		$end_date = new \DateTime();
		$now = new \DateTime('NOW');

		$start_date->setDate($start_date_array[2], $start_date_array[0], $start_date_array[1]);
		$end_date->setDate($end_date_array[2], $end_date_array[0], $end_date_array[1]);

		$invite = new Invite(rand());

		$invite->setSubject($showpages->getShowTitle());
		$invite->setName($showpages->getShowTitle());
		$invite->setDescription($showpages->getShowDescription());
		$invite->setCreated($now);
		$invite->setStart($start_date);
		$invite->setEnd($end_date);
		$invite->setLocation($showpages->getShowCity().','.$showpages->getShowState());
		$invite->download();
	}

}
?>

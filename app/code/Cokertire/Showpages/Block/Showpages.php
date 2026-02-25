<?php
namespace Cokertire\Showpages\Block;

use Magento\Framework\UrlInterface;

class Showpages extends \Magento\Framework\View\Element\Template
{

	protected $_showpagesCollectionFactory;

	private $urlBuilder;

	protected $helper;

	public $_storeManager;

	protected $timezone;

	public function __construct(\Magento\Framework\View\Element\Template\Context              $context,
                                \Cokertire\Showpages\Helper\Data                              $helper,
                                UrlInterface                                                  $urlBuilder,
                                \Magento\Framework\Stdlib\DateTime\TimezoneInterface          $timezone,
                                \Magento\Store\Model\StoreManagerInterface                    $storeManager,
                                \Cokertire\Showpages\Model\ResourceModel\Showpages\Collection $_showpagesCollectionFactory
		)
	{
		$this->_storeManager=$storeManager;
		$this->_showpagesCollectionFactory = $_showpagesCollectionFactory;
		$this->urlBuilder = $urlBuilder;
		$this->timezone = $timezone;
		$this->helper = $helper;

		parent::__construct($context);
	}

	public function getShowpages(){
		$collection = $this->_showpagesCollectionFactory->addFieldToFilter("status","1");
		$array =  $collection->getData();
		return $this->helper->ArraySort($array, 'show_date', SORT_ASC);
	}

	public function getViewUrl($id){
       return $this->getBaseUrl()."shows/".$id;
	}

	public function getBaseUrl(){
		return $this->_storeManager->getStore()->getBaseUrl();
	}

    public function getShowDate($showpage)
    {
        $date = explode("/", $showpage['show_date']);
        $date2 = explode("/", $showpage['show_date_end']);
        return substr(date('F', strtotime($showpage['show_date'])), 0, 3).' '.$date[1].' - '.$date2[1];
    }
    public function getShowYear($showpage)
    {
        $date = explode("/", $showpage['show_date']);
        return $date[2];
    }
	public function getDownUrl($id){
       return $this->urlBuilder->getUrl('showpages/index/down',array("id"=>$id));
	}
	public function formateDate($date,$pattern)
	{
		return $this->getDate($date)->format($pattern);
	}
	protected function getDate($date=null,$timezone=null){
        $input = $date?$date:"now";
        if ($timezone == null) {
            $timezone = $this->timezone->getConfigTimezone()
            ? $this->timezone->getConfigTimezone()
            : @date_default_timezone_get();
           return  new \DateTime($input, new \DateTimeZone($timezone));
        }elseif(is_string($timezone)){
            return  new \DateTime($input, new \DateTimeZone($timezone));
        }elseif($timezone instanceof \DateTimeZone){
            return  new \DateTime($input, $timezone);
        }
    }

}

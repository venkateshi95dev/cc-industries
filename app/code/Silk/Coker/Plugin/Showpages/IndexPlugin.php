<?php
namespace Silk\Coker\Plugin\Showpages;
class IndexPlugin
{
    protected $helper;
    public function __construct(
        \Silk\Coker\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }
    public function afterExecute(
        \Cokertire\Showpages\Controller\Index\Index $controller,
        $resultPage
    ){
        $resultPage->getConfig()->getTitle()->set($this->helper->getStoreConfig('showpagesconfig/general/page_title'));
        return $resultPage;
    }
}


 ?>

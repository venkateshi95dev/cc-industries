<?php

namespace Silk\Coker\Controller\Search;

use Magento\Framework\App\Action\Context;

class Searchbysize extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    private \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory;


    public function __construct(Context $context,
     \Magento\Framework\Registry $registry,
     \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
     \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
     \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
     \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    protected readonly \Magento\Framework\App\ResourceConnection $resourceConnection
     )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeInterface;
        $this->resultJsonFactory            = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        $result  = $this->resultJsonFactory->create();
  /*      $attrIds = array (
                    "tire_rim_diameter" => 526,
                    "size_section_width" => 572,
                    "shop_aspect_ratio_radial" => 674,
                    "sidewall_style" => 525,
                    "tire_build" => 630
                );*/
        $postaction = $this->getRequest()->getParam('action');
        if($postaction == 'getAllIds') {
            $write = $this->resourceConnection->getConnection();
            $vars = $this->getRequest()->getParam('vars');
            $qString= array();
            $returnArr = array();
            foreach($vars as $attr => $q){
                if($q != '00'){
                    switch($attr)
                    {
                        case 'tire_build':
                        {
                            array_push($returnArr, 'tire_build='.$q);
                            break;
                        }
                        case 'sidewall_style':
                        {
                            array_push($returnArr, 'sidewall_style='.$q);
                            break;
                        }
                        case 'size_section_width_radial':
                        {
                            array_push($returnArr, 'size_section_width_radial='.$q);
                            break;
                        }
                        case 'shop_width_bias_ply':
                        {
                            array_push($returnArr, 'shop_width_bias_ply='.$q);
                            break;
                        }
                        case 'shop_width_bias_look':
                        {
                            array_push($returnArr, 'shop_width_bias_look='.$q);
                            break;
                        }
                        case 'shop_aspect_ratio_radial':
                        {
                            array_push($returnArr, 'shop_aspect_ratio_radial='.$q);
                            break;
                        }
                        case 'tire_rim_diameter':
                        {
                            array_push($returnArr, 'tire_rim_diameter='.$q);
                            break;
                        }
                    }
                }
            }

            $r = implode('&',$returnArr);
                    if(count($returnArr) >= 1) {
                        array_push($qString, $r);
                    }

            $t = implode('&',$qString);

            if(count($qString) < 1) {
                $t =  'false';
                return $result->setData($t);
            } else {
                //echo $t;
                return $result->setData($t);
            }
        }
    }

    public function getErrorMessage($error,$regenerate){
        return array("error"=>$error,"regenerate"=>$regenerate);
    }

    public function getSuccessMessage($success,$regenerate){
        return array("success"=>$success,"regenerate"=>$regenerate);
    }

}

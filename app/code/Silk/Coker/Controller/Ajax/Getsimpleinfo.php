<?php

namespace Silk\Coker\Controller\Ajax;

use Magento\Framework\App\Action\Context;

class Getsimpleinfo extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(Context $context,
     \Magento\Framework\Registry $registry,
     \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
     \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
     \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
     \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    protected readonly \Magento\Catalog\Model\ProductFactory $productFactory,
    protected readonly \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
     )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

      public function execute()
      {
        $result  = $this->resultJsonFactory->create();
        $output = [];
        $sku = $this->getRequest()->getParam('sku');
        $simpleid = $this->getRequest()->getParam('simpleid');


        if(isset($sku) || isset($simpleid))
        {
                $_catalog = $this->productFactory->create();
                $_productId = $_catalog->getIdBySku($sku);
                if($_productId){ $thisid = $_productId; }
                if($simpleid){ $thisid = $simpleid; }
                $_product = $this->productFactory->create();
                $_product->load($thisid);
                $_related_items = $_product->getRelatedProductIds();
                $_images = $_product->getMediaGalleryImages();
                $output['specs'] = '   <div class="additional-attributes-wrapper table-wrapper">
                        <table class="data table additional-attributes" id="product-attribute-specs-table">
                            <caption class="table-caption">More Information</caption>
                            <tbody>';
                $attributes = $_product->getAttributes();
                foreach ($attributes as $attribute) {
                    if ($attribute->getIsVisibleOnFront()) {
                        if($attribute->getFrontend()->getValue($_product) == 'No' || $attribute->getFrontend()->getValue($_product) == '' || $attribute->getFrontend()->getValue($_product) == null){
                            // do not output anything if an attribute is blank
                        } else {
                            $output['specs'] .= '<tr><th class="col attr_label" scope="row">'.$attribute->getStoreLabel().'</th><td class="col data" data-th="'.$attribute->getStoreLabel().'">'.$attribute->getFrontend()->getValue($_product).'</td></tr>';
                        }
                    }
                }

                $output['specs'] .= '</tbody></table></div>';


                if(count($_related_items) > 0) {
                    // limit related items to a max of 6 displayed
                    $sliced = array_slice($_related_items, 0, 6);
                    $output['related'] = null;
                   // $output['related'] = '<div id="block-related" class="mini-products-list padding_top1 padding_btm1 clearfix">';
                    foreach ($sliced as $item) {

                        // load each related item
                        $related_item = $this->productFactory->create();
                        $related_item->load($item);

                        // don't show any of the virtual product types (ETP or EWP)
                        if ($related_item->getTypeId() == "virtual") {
                            continue;
                        }
                        if ($related_item->getTypeId() == "grouped") {
                          $aProductIds = $related_item->getTypeInstance()->getChildrenIds($related_item->getId());
                          $prices = array();
                          foreach ($aProductIds as $ids) {
                          foreach ($ids as $id) {
                          $aProduct =  $this->productFactory->create()->load($id);
                          $oneprice =  $aProduct->getPriceModel()->getPrice($aProduct);
                           $prices[] = substr(sprintf("%.3f",$oneprice),0,-1);
                          }
                          }
                          krsort($prices);
                          $related_item->setFinalPrice(array_shift($prices));
                          $finalprice = "$".$related_item->getFinalPrice();
                          $finalprice ='<span class="price-label">Starting at</span><span class="price">'. $finalprice . '</span></span>';
                      }else{
                          $finalprice = "$".$related_item->getFinalPrice();
                          $finalprice ='<span class="price">' .$finalprice . '</span></span>';
                      }



                        // limit product name to 160 characters to keep things from getting crazy
                        $prod_name = htmlspecialchars ($related_item->getName());
                        if (strlen ($prod_name) > 10) {
                            $prod_name = substr ($prod_name, 0, 160);
                        }
                        $img = $this->productMediaConfig->getMediaUrl($related_item->getImage());
$output['related'] .= '<li class="item product product-item" style=""><div class="product-item-info related-available">
 <a href="' . $related_item->getProductUrl() . '" class="product photo product-item-photo">
<span class="product-image-container" style="width:152px;">
<span class="product-image-wrapper" style="padding-bottom: 100%;">
<img class="product-image-photo" src="' . $img . '" width="152" height="152" alt="' . $prod_name . '"></span>
</span></a><div class="product details product-item-details"><strong class="product name product-item-name"><a class="product-item-link" title="' . $prod_name . '" href="' . $related_item->getProductUrl() . '">
' . $prod_name . '</a></strong><div class="price-box price-final_price" data-role="priceBox" data-product-id="'.$item.'">
<span class="price-container price-final_price tax weee">
<span id="product-price-'.$item.'" data-price-amount="' . $related_item->getFinalPrice() . '" data-price-type="finalPrice" class="price-wrapper ">'.$finalprice.'
</span>
</div>
</div>
</div>
</li>';
                    }
                   // $output['related'] .= '</div>';

                } else {
                    // show a message that there are no related items if none are set
                    $output['related'] = __('<div id="block-related" class="mini-products-list padding_top1 padding1 clearfix">There are no related products for this item</div>');
                }


                $bigimg = $this->productMediaConfig->getMediaUrl($_product->getImage());

                $output["image"] = '<div class="MagicToolboxContainer selectorsBottom minWidth">
<div id="mtImageContainer" style="display: block;">
<div><a id="MagicZoomPlusImage-product-2420" class="MagicZoom"><figure class="mz-figure mz-hover-zoom mz-ready"><img itemprop="image" src="'.$bigimg.'">
<div class="mz-lens" style="top: 0px; transform: translate(-10000px, -10000px); width: 54px; height: 54px;">
<img src="'.$bigimg.'" style="position: absolute; top: 0px; left: 0px; transform: translate(-26.3594px, -8px);">
</div>
<div class="mz-loading"></div>
<div class="mz-hint mz-hint-hidden"><span class="mz-hint-message">Click to expand</span></div></figure></a></div></div>
<div id="mt360Container" style="display: none;">
</div><div id="mtVideoContainer" style="display: none;">
</div>
<div class="MagicToolboxSelectorsContainer">
<div id="MagicToolboxSelectors2420" class="MagicScroll MagicScroll-arrows-inside MagicScroll-horizontal" data-options="autostart:false;" data-mode="scroll" style="visibility: visible; display: inline-block; width: 100%; height: 104px; overflow: visible;">
<div class="mcs-loader" style="visibility: hidden; opacity: 0; display: none;">
<div class="mcs-loader-circles">
<div class="mcs-loader-circle mcs-loader-circle_01"></div>
<div class="mcs-loader-circle mcs-loader-circle_02"></div>
<div class="mcs-loader-circle mcs-loader-circle_03"></div>
<div class="mcs-loader-circle mcs-loader-circle_04"></div>
<div class="mcs-loader-circle mcs-loader-circle_05"></div>
<div class="mcs-loader-circle mcs-loader-circle_06"></div>
<div class="mcs-loader-circle mcs-loader-circle_07"></div>
<div class="mcs-loader-circle mcs-loader-circle_08"></div>
</div>
</div>
<div class="mcs-wrapper" style="display: inline-block;">
<div class="mcs-items-container" style="white-space: nowrap; transform: translate3d(0px, 0px, 0px); transition: transform 0ms;">';
if(count($_images) > 0) {
    $image_array = [];
    foreach ($_images as $image) {
        $image_array[] = [
            'src' => $image->getUrl(),
            'label' => $image->getLabel()
        ];
    }
    if(count($image_array)){
        $small_images = array_slice($image_array, 0, 3);
        foreach($small_images as $key => $sm) {
             $output['image'] .= '<div class="mcs-item" data-item="'.$key.'" style="width: "25%"><a class="mt-thumb-switcher mz-thumb myimage" data-zoom-id="MagicZoomPlusImage-product-2420"   title="' . $sm['label'] . '" style="opacity: 1; visibility: visible;"><img src="' . $sm['src'] . '" alt="' . $sm['label'] . '" ></a></div>';
        }
    }

}
$output["image"] .='</div></div>
<button type="button" class="mcs-button mcs-horizontal mcs-button-arrow mcs-button-arrow-prev mcs-disabled mcs-hidden" style="display: inline-block;"></button><button type="button" class="mcs-button mcs-horizontal mcs-button-arrow mcs-button-arrow-next mcs-hidden mcs-disabled" style="display: inline-block;"></button></div>
</div>
</div>';
            return $result->setData($output);
        } else {
            $output['error'] = __('Please choose a product');
            return $result->setData($output);
        }
        }

      }

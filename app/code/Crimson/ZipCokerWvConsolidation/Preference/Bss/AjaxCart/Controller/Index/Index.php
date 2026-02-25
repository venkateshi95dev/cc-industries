<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Bss\AjaxCart\Controller\Index;

use Bss\AjaxCart\Block\Ajax\Template;
use Bss\AjaxCart\Block\Popup\Suggest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Index extends \Bss\AjaxCart\Controller\Index\Index
{
    protected function addProductsById($related)
    {
        if (!empty($related)) {
            $this->relatedAdded = true;
            if($this->getRequest()->getParam('qty'.$related)){
                $relatedqty = $this->getRequest()->getParam('qty'.$related);
                $relatedparams = array("qty"=>$relatedqty);
                $relatedproduct = $this->_initRelatedProduct($related);
                $this->cart->addProduct($relatedproduct, $relatedparams);
            }else{
                $this->cart->addProductsByIds(explode(',', $related));
            }
        }
    }
    protected function _initRelatedProduct($related)
    {
        $productId = (int)$related;
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
    protected function returnResult($resultItem, $relatedAdded, array $additionalInfo = [])
    {
        if (!$this->cart->getQuote()->getHasError()) {
            $result = [];

            $popupTemplate = 'Bss_AjaxCart::popup.phtml';

            $params = $this->getRequest()->getParams();
            $productId = $params['id'] ?? $resultItem->getProductId();
            foreach ($this->cart->getQuote()->getAllVisibleItems() as $item){
                if($productId == $item->getProduct()->getId())
                    $resultItem = $item;
            }

            $resultPage = $this->resultPageFactory->create();
            $popupBlock = $resultPage->getLayout()
                ->createBlock(Template::class)
                ->setTemplate($popupTemplate)
                ->setItem($resultItem)
                ->setRelatedAdded($relatedAdded);

            if ($this->ajaxHelper->isShowSuggestBlock()) {
                $suggestTemplate = 'Bss_AjaxCart::popup/suggest.phtml';
                $suggestBlock = $resultPage->getLayout()
                    ->createBlock(Suggest::class)
                    ->setTemplate($suggestTemplate)
                    ->setProductId($productId);

                $popupAjaxTemplate = 'Bss_AjaxCart::popup/ajax.phtml';
                $popupAjaxBlock = $resultPage->getLayout()
                    ->createBlock(Template::class)
                    ->setTemplate($popupAjaxTemplate);

                $suggestBlock->setChild('ajaxcart.popup.ajax.suggest', $popupAjaxBlock);
                $popupBlock->setChild('ajaxcart.popup.suggest', $suggestBlock);
            }

            $html = $popupBlock->toHtml();

            $message = __(
                'You added %1 to your shopping cart.',
                $resultItem->getName()
            );
            $this->messageManager->addSuccessMessage($message);

            $result['popup'] = $html;
            unset($additionalInfo['form_key']);
            $result = array_merge(
                $result,
                $additionalInfo
            );

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        }
    }
}

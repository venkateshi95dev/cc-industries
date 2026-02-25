<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AjaxCart\Controller\Index;

use Bss\AjaxCart\Block\Ajax\Template;
use Bss\AjaxCart\Block\Popup\Suggest;
use Bss\AjaxCart\Filter\LocalizedToNormalized;
use Bss\AjaxCart\Helper\Data;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\Wishlist;
use Psr\Log\LoggerInterface;
use Zend_Locale_Exception;

/**
 * Class Index
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Variable Form key validator
     *
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Customer cart
     *
     * @var CustomerCart
     */
    protected $cart;

    /**
     * Resolver.
     *
     * @var ResolverInterface
     */
    protected $resolverInterface;

    /**
     * Variable Escaper.
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $loggerInterface;

    /**
     * Variable Result page factory.
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Variable product repository.
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Variable store manager.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Ajax cart helper.
     *
     * @var Data
     */
    protected $ajaxHelper;

    /**
     * Variable localized to normalized.
     *
     * @var LocalizedToNormalized
     */
    protected $localizedToNormalized;

    /**
     * Variable data object factory.
     *
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Core registry.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Url builder.
     *
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var bool
     */
    protected $relatedAdded = false;
    /**
     * @var Wishlist
     */
    protected $wishlist;
    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var OptionFactory
     */
    private $optionFactory;
    /**
     * @var Product
     */
    private $productHelper;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ResolverInterface $resolverInterface
     * @param Escaper $escaper
     * @param LoggerInterface $loggerInterface
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param Data $ajaxHelper
     * @param LocalizedToNormalized $localizedToNormalized
     * @param DataObjectFactory $dataObjectFactory
     * @param Registry $registry
     * @param Session $customerSession
     * @param ItemFactory $itemFactory
     * @param OptionFactory $optionFactory
     * @param Product $productHelper
     * @param Wishlist $wishlist
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                    $context,
        Validator                  $formKeyValidator,
        CustomerCart               $cart,
        ResolverInterface          $resolverInterface,
        Escaper                    $escaper,
        LoggerInterface            $loggerInterface,
        PageFactory                $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface      $storeManager,
        Data                       $ajaxHelper,
        LocalizedToNormalized      $localizedToNormalized,
        DataObjectFactory          $dataObjectFactory,
        Registry                   $registry,
        Session                    $customerSession,
        ItemFactory                $itemFactory,
        OptionFactory              $optionFactory,
        Product                    $productHelper,
        Wishlist                   $wishlist
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->cart = $cart;
        $this->resolverInterface = $resolverInterface;
        $this->escaper = $escaper;
        $this->loggerInterface = $loggerInterface;
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->ajaxHelper = $ajaxHelper;
        $this->localizedToNormalized = $localizedToNormalized;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->urlInterface = $context->getUrl();
        $this->itemFactory = $itemFactory;
        $this->optionFactory = $optionFactory;
        $this->productHelper = $productHelper;
        $this->wishlist = $wishlist;
    }

    /**
     * Commit lan 1
     *
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if (!$this->ajaxHelper->isEnabled()) {
            return;
        }
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        $additionalInfo = [];
        $params = $this->getRequest()->getParams();
        $specifyOptions = $this->getRequest()->getParam('specifyOptions');
        if ($specifyOptions) {

            $product = $this->initProduct();
            $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
            $this->messageManager->addNoticeMessage(
                $this->escaper->escapeHtml($notice)
            );
            $result = [];
            $result['error'] = true;
            $result['view'] = true;
            $result['message'] = false;
            $result['url'] = $this->escaper->escapeUrl(
                $this->urlInterface->getUrl('ajaxcart/index/view', ['id' => $params['id']])
            );
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        }
        try {
            $params = $this->setQtyParams($params);
            $product = $this->initProduct();
            $additionalInfo['product_id'] = $product->getId();

            if (!$product) {
                return $this->resultRedirectFactory->create()->setPath('/');
            }
            $data = [
                'status' => true,
                'added' => false,
                'messages' => []
            ];
            $result = $this->dataObjectFactory->create()->setData($data);
            $this->_eventManager->dispatch(
                'bss_ajaxcart_add_before',
                ['product' => $product, 'request' => $this->getRequest(), 'result' => $result]
            );
            if (!$result->getData('status') && empty($messages)) {
                return $this->resultRedirectFactory->create()->setPath('/');
            }
            $messages = $result->getData('messages');

            if (!empty($messages)) {
                throw new LocalizedException(
                    $messages[0]['message']
                );
            }
            if (isset($params['cmsName']) && $params['cmsName'] == 'wishlist_index_index') {
                $item = $this->itemFactory->create()->load($params['item']);
                $item->setQty($params['qty']);
                $additionalInfo['wishlist_item'] = $params['item'];

                $options = $this->optionFactory->create()->getCollection()->addItemFilter($params['item']);
                $item->setOptions($options->getOptionsByItem($params['item']));
                $buyRequest = $this->productHelper->addParamsToBuyRequest(
                    $this->getRequest()->getParams(),
                    ['current_config' => $item->getBuyRequest()]
                );
                $item->mergeBuyRequest($buyRequest);
                $item->addToCart($this->cart, true);
                $this->cart->save()->getQuote()->collectTotals();
            } else {
                $this->addProduct($result, $product, $params);
                $related = $this->getRequest()->getParam('related_product');
                $messages = $result->getData('messages');
                foreach ($messages as $message) {
                    $this->addResultMessage($message);
                }
                $this->addProductsById($related);
                $this->cart->save();

                // Quick view was showed in wishlist page
                // when try add to cart a product with required options (like configurable products, custom options...)
                // params have no 'cmsName' = 'wishlist_index_index'
                // so, we do check if params have 'added_from_wishlist' = 1
                // then remove this item from wishlist
                if (isset($params['added_from_wishlist']) &&
                    $params['added_from_wishlist'] == 1) {
                    $customerId = $this->customerSession->getCustomer()->getId();
                    if ($customerId) {
                        $currentCustomerWishlist = $this->wishlist->loadByCustomerId($customerId);
                        $wishlistItems = $currentCustomerWishlist->getItemCollection();

                        foreach ($wishlistItems as $wishlistItem) {
                            if ($wishlistItem->getProductId() == $product->getId()) {
                                $additionalInfo['wishlist_item'] = $wishlistItem->getId();
                                $wishlistItem->delete();
                            }
                        }
                    }
                }
            }

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
            if ($product->getTypeId() == Grouped::TYPE_CODE) {
                $resultItem = $this->dataObjectFactory->create()->setProduct($product);
            } else {
                $resultItem = $this->registry->registry('last_added_quote_item');
            }
            return $this->returnResult($resultItem, $this->relatedAdded, $additionalInfo);
        } catch (LocalizedException $e) {
            $this->messageManager->addNoticeMessage(
                $this->escaper->escapeHtml($e->getMessage())
            );
            $result = [];
            $result['error'] = true;
            $result['view'] = true;
            $result['message'] = $e->getMessage();
            $result['url'] = $this->escaper->escapeUrl(
                $this->urlInterface->getUrl('ajaxcart/index/view', ['id' => $params['id']])
            );
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->loggerInterface->critical($e);
            $result = [];
            $result['error'] = true;
            $result['message'] = $e->getMessage();
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        }
    }

    /**
     * Init requested product.
     *
     * @return bool|ProductInterface
     */
    protected function initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');

        if (!$productId) {
            $productId = (int)$this->getRequest()->getParam('id');
        }

        if ($productId) {
            try {
                $storeId = $this->storeManager->getStore()->getId();
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Function set qty params
     *
     * @param $params
     * @return mixed
     * @throws Zend_Locale_Exception
     */
    protected function setQtyParams($params)
    {
        if (array_key_exists('qty', $params ?? [])) {
            // Mixed wish list check qty
            $qty = $val = $params['qty'];
            $id = $params['id'];
            $filter = $this->localizedToNormalized;
            if (is_array($qty) && isset($qty[$id])) {
                $val = $qty[$id];
                $valAfter = $filter->filter($val);
                $qty[$id] = $valAfter;
            } elseif (is_string($qty) || is_int($qty)) {
                $qty = $filter->filter($val);
            }
            $params['qty'] = $qty;
        }
        return $params;
    }

    /**
     * Add product
     *
     * @param $result
     * @param $product
     * @param $params
     * @throws LocalizedException
     */
    protected function addProduct($result, $product, $params)
    {
        if (!$result->getData('added')) {
            $this->cart->addProduct($product, $params);
        }
    }

    /**
     * Add message from bss_ajaxcart_add_before result
     *
     * @param array $message
     * @return void
     */
    protected function addResultMessage(array $message)
    {
        if (isset($message['type'])) {
            switch ($message['type']) {
                case "notice":
                    $this->messageManager->addNoticeMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
                    break;
                case "error":
                    $this->messageManager->addErrorMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
                    break;
                case "success":
                    $this->messageManager->addSuccessMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
                    break;
                default:
                    $this->messageManager->addNoticeMessage(
                        $this->escaper->escapeHtml($message['message'])
                    );
            }
        }
    }

    /**
     * Add product by id
     *
     * @param $related
     */
    protected function addProductsById($related)
    {
        if (!empty($related)) {
            $this->relatedAdded = true;
            $this->cart->addProductsByIds(explode(',', $related));
        }
    }

    /**
     * Return add to cart result.
     *
     * @param Item|object $resultItem
     * @param boolean $relatedAdded
     * @param array $additionalInfo
     * @return ResultInterface|void
     */
    protected function returnResult($resultItem, $relatedAdded, array $additionalInfo = [])
    {
        if (!$this->cart->getQuote()->getHasError()) {
            $result = [];

            $popupTemplate = 'Bss_AjaxCart::popup.phtml';

            $params = $this->getRequest()->getParams();
            $productId = $params['id'] ?? $resultItem->getProductId();

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

<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Helper\Data as EbizDataHelper;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Plugin\SubscribeOptions;
use Exception;
use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;

/**
 * Get current quote and item class
 *
 * Class Quote
 */
class Quote
{
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var BackendQuote
     */
    protected BackendQuote $backendQuoteSession;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var RecurringRepository
     */
    protected RecurringRepository $_recurringRepository;

    /**
     * @var Session
     */
    protected Session $_customerSession;


    /**
     * @var Option
     */
    protected Option $_quoteOptionsModel;

    /**
     * @var Item
     */
    protected Item $_quoteItem;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $_itemCollectionFactory;

    /**
     * @var Json
     */
    protected Json $_jsonModel;

    /**
     * @var QuoteModel
     */
    protected QuoteModel $_quoteModel;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * Quote constructor.
     *
     * @param AttributeValueFactory $customAttributeFactory
     * @param QuoteModel $quoteModel
     * @param CheckoutSession $checkoutSession
     * @param BackendQuote $backendQuoteSession
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RecurringRepository $recurringRepository
     * @param Item $quoteItem
     * @param CollectionFactory $itemCollectionFactory
     * @param Json $jsonModel
     * @param Option $quoteOptionsModel
     * @param Session $customerSession
     * @param EbizchargeLogger $ebizchargeLogger
     * @param RequestInterface $request
     */
    public function __construct(
        AttributeValueFactory $customAttributeFactory,
        QuoteModel            $quoteModel,
        CheckoutSession       $checkoutSession,
        BackendQuote          $backendQuoteSession,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RecurringRepository   $recurringRepository,
        Item                  $quoteItem,
        CollectionFactory     $itemCollectionFactory,
        Json                  $jsonModel,
        Option                $quoteOptionsModel,
        Session               $customerSession,
        EbizchargeLogger      $ebizchargeLogger,
        RequestInterface      $request
    )
    {

        /** @var  _quoteModel */
        $this->_quoteModel = $quoteModel;
        /** @var  checkoutSession */
        $this->checkoutSession = $checkoutSession;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  backendQuoteSession */
        $this->backendQuoteSession = $backendQuoteSession;
        /** @var  _customerSession */
        $this->_customerSession = $customerSession;
        /** @var _recurringRepository */
        $this->_recurringRepository = $recurringRepository;
        /** @var
         * _searchCriteriaBuilder
         */
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;

        /** @var  _quoteOptionsModel */
        $this->_quoteOptionsModel = $quoteOptionsModel;

        /** @var  _quoteItem */
        $this->_quoteItem = $quoteItem;
        /** @var  _itemCollectionFactory */
        $this->_itemCollectionFactory = $itemCollectionFactory;
        /** @var  _jsonModel */
        $this->_jsonModel = $jsonModel;
        /** @var  _request */
        $this->_request = $request;
    }

    /**
     * Is Subscription Already Exists
     *
     * @param array $recurringParams
     * @return bool
     */
    public function isSubscriptionAlreadyExists($recurringParams = [])
    {
        $isRecurringExists = false;

        $productId = isset($recurringParams['product']) ? $recurringParams['product'] : 0;
        $customerId = $this->_customerSession->getCustomerId();
        /**
         * Recurring Data Exists
         */
        $reucrringData = isset($recurringParams['recurring']) ? $recurringParams['recurring'] : [];

        /**
         * if recurring date is OK
         */
        $newSdate = isset($reucrringData['sdate']) ? $reucrringData['sdate'] : '';

        /**
         * check if product is already subscribed
         */
        if ($productId && $customerId) {
            $canceledStatus = RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED;

            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(RecurringInterface::MAGE_CUST_ID, $customerId)
                ->addFilter(RecurringInterface::MAGE_ITEM_ID, $productId);

            /**
             * recurring product Entries
             */
            $recurringDataEntries = $this->_recurringRepository->getList($searchCriteria->create());

            if ($newSdate && count($recurringDataEntries->getItems()) > 0) {
                foreach ($recurringDataEntries->getItems() as $recurringData) {
                    if ((int)$recurringData->getData(RecurringInterface::REC_STATUS) === (int)RecurringInterface::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
                        continue;
                    }

                    $oldRecurringStartDate = date(
                        'Y-m-d',
                        strtotime($recurringData->getData('eb_rec_start_date'))
                    );
                    $oldRecurringEndDate = date(
                        'Y-m-d',
                        strtotime($recurringData->getData('eb_rec_end_date'))
                    );
                    $newRecurringDate = date('Y-m-d', strtotime($newSdate));

                    if (($newRecurringDate >= $oldRecurringStartDate) &&
                        ($newRecurringDate <= $oldRecurringEndDate)) {
                        $isRecurringExists = true;
                    }
                }
            }
        }

        return $isRecurringExists;
    }


    /**
     * Get Subscribed Options By Item Id
     *
     * @param null|mixed $quoteItemId
     * @return array
     */
    public function getItemOptionsById($quoteItemId = null): array
    {
        $subscriptionData = [
            'option_id' => '',
            'value' => []
        ];
        if (!$quoteItemId) {
            return $subscriptionData;
        }

        $itemOptions = $this->getItemInfoBuyRequest($quoteItemId);
        if (count($itemOptions) > 0) {
            foreach ($itemOptions as $option) {
                $subscriptionData = [
                    'option_id' => $option->getId(),
                    'value' => $this->_jsonModel->unserialize($option->getValue())
                ];
            }
        }
        return $subscriptionData;
    }

    /**
     * Get Item Info Buy Requests
     *
     * @param null|mixed $itemId
     * @return AbstractDb|AbstractCollection|array|null
     */
    public function getItemInfoBuyRequest($itemId = null)
    {
        if (!$itemId) {
            return [];
        }
        return $this->_quoteOptionsModel->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('item_id', $itemId);
    }

    /**
     * Get Subscribed Quote Items Data db
     *
     * @param null|mixed $quoteItemId
     * @param null|mixed $quoteProductId
     * @return mixed
     */
    public function getSubscribedQuoteItemsDatadb($quoteItemId = null, $quoteProductId = null)
    {
        $recurringDataEntries = $this->_quoteOptionsModel->getCollection()
            ->addFilter('item_id', $quoteItemId)
            ->addFilter('product_id', $quoteProductId);

        $recurringItemOptions = null;

        if (count($recurringDataEntries) > 0) {
            foreach ($recurringDataEntries as $recurringDataEntry) {
                return $recurringDataEntry;
            }
        }
        return $recurringItemOptions;
    }

    /**
     * @param $quoteItems
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function reArrangeSubscribedCartItems($quoteItems = [])
    {


        $newReArrangedQuoteItems = [];
        $subscribedItems = [];
        $unSubscribedItems = [];


        /** @var  $quoteItems */
        if (count($quoteItems) > 0) {
            foreach ($quoteItems as $index => $quoteItem) {
                $buyRequestData = $quoteItem->getBuyRequest();
                $recurringData = $buyRequestData->getRecurring() ?? [];
                $recurringData = (array)$recurringData;
                if (isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency'])) {
                    $subscribedItems[] = $quoteItem;
                }else{
                    $unSubscribedItems[] = $quoteItem;
                }
            }
        }

        return array_merge($unSubscribedItems, $subscribedItems);
    }

    /**
     * Get current quote
     *
     * @return CartInterface|QuoteModel|null
     */
    public function getQuote()
    {
        try {
            if ($this->isAdmin()) {
                return $this->backendQuoteSession->getQuote();
            }
            return $this->checkoutSession->getQuote();
        } catch (Exception $e) {
            /** logging error and exception */
            $this->ebizchargeLogger->addError(__("Exception occured " . $e->getMessage()));
        }
        return null;
    }

    /**
     * Check if admin side
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isAdmin(): bool
    {
        return EbizDataHelper::getAreaCode() == Area::AREA_ADMINHTML;
    }

    /**
     * Is Subscription Already Exists
     *
     * @param array $recurringParams
     * @return bool
     */
    public function isSubscriptionExistsBeforeItemPlugin($recurringParams = [])
    {
        /** @var  $isRecurringExists */
        $isRecurringExists = false;
        $productId = isset($recurringParams['product']) ? $recurringParams['product'] : 0;
        $customerId = $this->_customerSession->getCustomerId();
        $isFromGroup = isset($recurringParams['is_group']) ? $recurringParams['is_group'] : false;
        /**
         * check if product is already subscribed
         */
        if ($productId && $customerId) {
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(RecurringInterface::MAGE_CUST_ID, $customerId)
                ->addFilter(RecurringInterface::MAGE_ITEM_ID, $productId);
            /**
             * recurring product Entries
             */
            $recurringDataEntries = $this->_recurringRepository->getList($searchCriteria->create());

            if (count($recurringDataEntries->getItems()) > 0 && $isFromGroup) {
                foreach ($recurringDataEntries->getItems() as $recurringData) {
                    if ((int)$recurringData->getData(RecurringInterface::REC_STATUS) === 3) continue;
                    $recurringStartDate = date(
                        'Y-m-d',
                        strtotime($recurringData->getData('eb_rec_start_date'))
                    );
                    $recurringEndDate = date(
                        'Y-m-d',
                        strtotime($recurringData->getData('eb_rec_end_date'))
                    );
                    $dateToday = date('Y-m-d', strtotime("+ 1 day"));

                    if (($dateToday >= $recurringStartDate) && ($dateToday <= $recurringEndDate)) {
                        $isRecurringExists = true;
                    }
                }
            }

        }
        return $isRecurringExists;
    }

    /**
     * Update Subscribed Product Qty
     *
     * @param null|mixed $cartObj
     */
    public function updateSubscribedProductsQty($cartObj = null)
    {
        $cartItems = $cartObj->getItems() ? $cartObj->getItems() : $cartObj->getCart()->getItems();

        if (count($cartItems) > 0) {
            foreach ($cartItems as $item) {
                $itemId = $item->getId();
                $buyRequest = $item->getBuyRequest()->getData();

                $buyRequest['recurring'] = isset($buyRequest['recurring']) ? $buyRequest['recurring'] : [];
                if (isset($buyRequest['recurring']) && !empty($buyRequest['recurring']['rec_activate'])) {
                    $buyRequest['recurring']['rec_activate'] = SubscribeOptions::ENABLE_RECURRING;

                    if (array_key_exists('rec_indefinitely', $buyRequest['recurring'])) {
                        $buyRequest['recurring']['rec_indefinitely'] = SubscribeOptions::REC_INDEFINITELY;
                    }

                    foreach ($item->getOptions() as $option) {
                        try {
                            $option = $option->load($option->getId());
                            if (isset($buyRequest['recurring']) && !empty($buyRequest['recurring']['rec_activate'])) {
                                if ($option->getValue() === "recurring") {
                                    $option->setValue($this->_jsonModel->serialize($buyRequest));
                                    $item->setOptions($option->getData());
                                    $option->save();
                                }
                            }

                        } catch (Exception $e) {
                            $this->ebizchargeLogger->addCritical(__("Error:" . $e->getMessage()));
                        }
                    }
                    $buyRequest["recurring"] = $this->_jsonModel->serialize($buyRequest["recurring"]);
                }
            }
        }
    }

    /**
     * @param $cartObj
     * @param $productRequestParams
     * @return void
     */
    public function updateSubscribedProductInCart($cartObj = null, $productRequestParams = [])
    {
        $cartItems = [];
        if ($cartObj->getId() !== null) {

            $cartItems = $cartObj->getItems() ? $cartObj->getItems() : [];
            if ($cartObj->getCart()) {
                $cartItems = $cartObj->getCart()->getItems();
            }
        }
        $delItemId = isset($productRequestParams["item_id"]) ? $productRequestParams["item_id"] : "";
        $editItemId = isset($productRequestParams["id"]) ? $productRequestParams["id"] : $delItemId;
        // var_dump("<pre> prod: ", $editItemId, $productRequestParams);

        if (count($cartItems) > 0) {
            foreach ($cartItems as $item) {
                $itemId = $item->getId();
                $productId = $item->getProductId();
                $buyRequest = $item->getBuyRequest()->getData();


                if (isset($productRequestParams["recurring"]["rec_activate"]) && $itemId == $editItemId) {
                    $buyRequest["recurring"] = $productRequestParams["recurring"];
                }

                if (!empty($buyRequest['recurring']) && isset($buyRequest['recurring']['rec_activate']) && $buyRequest['recurring']['rec_activate'] !== "0") {
                    $buyRequest['recurring']['rec_activate'] = SubscribeOptions::ENABLE_RECURRING;
                }
                if (array_key_exists('rec_indefinitely', $buyRequest['recurring'])) {
                    $buyRequest['recurring']['rec_indefinitely'] = SubscribeOptions::REC_INDEFINITELY;
                }

                foreach ($item->getOptions() as $option) {
                    try {
                        $option = $option->load($option->getId());

                        if (isset($buyRequest['recurring']) && !empty($buyRequest['recurring']['rec_activate'])) {
                            if ($option->getValue() === "recurring") {
                                $option->setValue($this->_jsonModel->serialize($buyRequest));
                                $item->setOptions($option->getData());
                                $option->save();
                            }
                        }

                    } catch (Exception $e) {
                        $this->ebizchargeLogger->addCritical(__("Error:" . $e->getMessage()));
                    }
                }
                $buyRequest["recurring"] = $this->_jsonModel->serialize($buyRequest["recurring"]);

            }

        }
    }

    /**
     * Save Quote Item Recurring Options
     *
     * @param null|mixed $optionsItemId
     * @param array $recurringDataParams
     * @return bool
     */
    private function saveQouteItemOptions($optionsItemId = null, $recurringDataParams = [])
    {
        try {
            $recurringDataParams = $this->_jsonModel->serialize($recurringDataParams);

            $quoteOptions = $this->_quoteOptionsModel->load($optionsItemId);
            $quoteOptions->setData('value', $recurringDataParams);
            $quoteOptions->save();

        } catch (Exception $e) {
            /** Logging the logs to the logger */
            $this->ebizchargeLogger->addCritical(__(
                "Exception occurred during updating the Cart Items " . $e->getMessage()
            ));
            return false;
        }
        return true;
    }


}

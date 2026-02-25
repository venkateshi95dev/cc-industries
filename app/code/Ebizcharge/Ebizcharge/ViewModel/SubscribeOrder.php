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

namespace Ebizcharge\Ebizcharge\ViewModel;

use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * Admin subscribe order items
 *
 * Class SubscribeOrder
 */
class SubscribeOrder implements ArgumentInterface
{
    /**
     * @var Quote
     */
    protected Quote $sessionQuote;
    /**
     * @var Create
     */
    protected Create $orderCreate;
    /**
     * @var Config
     */
    private Config $ebizConfig;
    /**
     * @var ItemFactory
     */
    private ItemFactory $quoteItemFactory;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $quoteOptionCollectionFactory;
    /**
     * @var Item
     */
    private Item $quoteItemResource;

    /**
     * @param Config $ebizConfig
     * @param ItemFactory $quoteItemFactory
     * @param Item $quoteItemResource
     * @param CollectionFactory $quoteOptionCollectionFactory
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param RequestInterface $request
     */
    public function __construct(
        Config            $ebizConfig,
        ItemFactory       $quoteItemFactory,
        Item              $quoteItemResource,
        CollectionFactory $quoteOptionCollectionFactory,
        Quote             $sessionQuote,
        Create            $orderCreate,
        RequestInterface  $request
    )
    {
        $this->ebizConfig = $ebizConfig;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quoteOptionCollectionFactory = $quoteOptionCollectionFactory;
        $this->request = $request;
        $this->quoteItemResource = $quoteItemResource;
        $this->sessionQuote = $sessionQuote;
        $this->orderCreate = $orderCreate;
    }

    /**
     * @return mixed
     */
    public function getQuoteData()
    {
        return $this->sessionQuote->getData();
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function isRecurringEnabled($storeId = null)
    {
        $storeId = $storeId ?? $this->sessionQuote->getStoreId();
        return $this->ebizConfig->isRecurringEnabled($storeId);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->sessionQuote->getCustomerId();

    }

    /**
     * Get Configured Frequencies
     *
     * @return void
     */
    public function getConfiguredFrequencies()
    {
        $this->ebizConfig->getRecurringFrequencyOptions($this->getSelectedFrequency());
    }

    /**
     * Get selected frequency
     *
     * @return string|null
     */
    public function getSelectedFrequency(): ?string
    {
        return $this->getSubscribedOptions()['rec_frequency'] ?? "";
    }

    /**
     * Get Subscribed Options
     *
     * @return null
     */
    public function getSubscribedOptions()
    {
        /** @var  $quoteItemId */
        $quoteItemId = $this->request->getParam('id');

        $quoteItem = $this->quoteItemFactory->create();
        $this->quoteItemResource->load($quoteItem, $quoteItemId);

        $quoteOptions = $this->quoteOptionCollectionFactory->create();
        $quoteOptions = $quoteOptions->getOptionsByItem($quoteItem);
        $quoteItem->setOptions($quoteOptions);

        return $quoteItem->getBuyRequest()['recurring'] ?? "";
    }

    /**
     * Get Product Id
     *
     * @return int
     */
    public function getProductId()
    {
        $quoteItemId = $this->request->getParam('id');
        $quoteItem = $this->quoteItemFactory->create()->load($quoteItemId);
        return $quoteItem->getProductId();
    }

    /**
     * Get Item ID
     *
     * @return mixed
     */
    public function getItemId()
    {
        return $this->request->getParam('id');
    }

    /**
     * Get subscription end date
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->getSubscribedOptions()['sdate'] ?? "";
    }

    /**
     * Get subscription end date
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->getSubscribedOptions()['edate'] ?? "";
    }

    /**
     * Product is subscribed or not
     *
     * @return bool
     */
    public function ifSubscribed(): bool
    {
        return !empty($this->getSubscribedOptions()['rec_activate']);
    }

    /**
     * @return bool
     */
    public function isProductSubscribed(): bool
    {
        $isProductSubscribed = false;
        $subscribedOptions = $this->getSubscribedOptions();
        if (isset($subscribedOptions["rec_activate"]) && (int)$subscribedOptions["rec_activate"] === 1) {
            $isProductSubscribed = true;

        }
        return $isProductSubscribed;

    }

    /**
     * Product is subscribed indefinitely
     *
     * @return int|null
     */
    public function recIndefinitely()
    {
        return $this->getSubscribedOptions()['rec_indefinitely'] ?? "";
    }
}

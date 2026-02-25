<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Observer;

use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Helper\PriceLevel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data as MQDataHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Observer to set product price level price before quote collect totals.
 */
class AdminQuoteCollectTotalsBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    public $data;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PriceLevel
     */
    public $helper;

    /**
     * @var MQDataHelper
     */
    public $dataHelper;

    /**
     * @var CurrencyFactory
     */
    public $priceCurrencyFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * QuoteCollectTotalsBefore constructor.
     *
     * @param LoggerInterface $logger
     * @param Data $data
     * @param MQDataHelper $dataHelper
     * @param PriceLevel $helper
     * @param CurrencyFactory $priceCurrencyFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct( // NOSONAR
        LoggerInterface $logger,
        Data $data,
        MQDataHelper $dataHelper,
        PriceLevel $helper,
        CurrencyFactory $priceCurrencyFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->data = $data;
        $this->helper = $helper;
        $this->dataHelper = $dataHelper;
        $this->priceCurrencyFactory = $priceCurrencyFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Set Erp tierprice to product
     *
     * @param Observer $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->data->isEnabled()) {
            return $this;
        }

        try {
            $quote = $observer->getQuote();
            $customerId = $quote->getCustomerId();
            $items = $quote->getAllVisibleItems();
            $this->helper->setItemPrice($items, $customerId);
            $quote->save();
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), LoggerInterface::I95EXC, 'critical');
            return $this;
        }
        return $this;
    }
}

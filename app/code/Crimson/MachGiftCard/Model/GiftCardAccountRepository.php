<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/7/2019 3:35 PM
 * @brief
 */

namespace Crimson\MachGiftCard\Model;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachGiftCard\Model\Api\GiftCardInfo;
use Crimson\MachGiftCard\Model\Service\MapGiftCards;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\GiftCardAccount\Api\Data\GiftCardAccountInterfaceFactory;
use Magento\GiftCardAccount\Api\Data\GiftCardAccountSearchResultInterface;
use Magento\GiftCardAccount\Api\Data\GiftCardAccountSearchResultInterfaceFactory;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\GiftCardAccount\Model\Spi\GiftCardAccountResourceInterface;
use Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class GiftCardAccountRepository
 * @package Crimson\MachGiftCard\Model
 */
class GiftCardAccountRepository extends \Magento\GiftCardAccount\Model\GiftCardAccountRepository
{
    const DATA_KEY_GIFTCARD_UPDATED_BY_MACH = 'giftcard_updated_by_mach';

    /**
     * @var GiftCardAccountResourceInterface
     */
    private $giftCardAccountResource;

    /**
     * @var GiftCardAccountInterfaceFactory
     */
    private $giftCardAccountFactory;

    /**
     * @var GiftCardAccountSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var GiftCardInfo
     */
    protected $giftCardInfo;
    /**
     * @var MachConfig
     */
    protected $machConfig;
    /**
     * @var MapGiftCards
     */
    protected $mapGiftCards;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param GiftCardAccountResourceInterface            $giftCardAccountResource
     * @param GiftCardAccountInterfaceFactory             $giftCardAccountFactory
     * @param CollectionFactory                           $collectionFactory
     * @param GiftCardAccountSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface                $collectionProcessor
     * @param MachConfig                                  $machConfig
     * @param GiftCardInfo                                $giftCardInfo
     * @param MapGiftCards                                $mapGiftCards
     * @param StoreManagerInterface  $storeManager
     * @param DataObjectHelper     $dataObjectHelper
     */
    public function __construct(
        GiftCardAccountResourceInterface $giftCardAccountResource,
        GiftCardAccountInterfaceFactory $giftCardAccountFactory,
        CollectionFactory $collectionFactory,
        GiftCardAccountSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        MachConfig $machConfig,
        GiftCardInfo $giftCardInfo,
        MapGiftCards $mapGiftCards,
        StoreManagerInterface $storeManager,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->giftCardAccountResource = $giftCardAccountResource;
        $this->giftCardAccountFactory  = $giftCardAccountFactory;
        $this->collectionFactory       = $collectionFactory;
        $this->searchResultFactory     = $searchResultFactory;
        $this->collectionProcessor     = $collectionProcessor;

        parent::__construct(
            $giftCardAccountResource, $giftCardAccountFactory, $collectionFactory, $searchResultFactory,
            $collectionProcessor
        );
        $this->machConfig       = $machConfig;
        $this->giftCardInfo     = $giftCardInfo;
        $this->storeManager     = $storeManager;
        $this->mapGiftCards     = $mapGiftCards;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param int $id
     * @return GiftCardAccountInterface|Giftcardaccount
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        /** @var Giftcardaccount $magentoGiftCard */
        $magentoGiftCard = parent::get($id);

        //we only get here if we loaded a card, if we don't have a card we can't do anything because $id is not the code.
        $this->_updateMagentoGiftCard($magentoGiftCard);
        if ($magentoGiftCard->isDeleted()) {
            return $this->giftCardAccountFactory->create();
        }

        return $magentoGiftCard;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return GiftCardAccountSearchResultInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = parent::getList($searchCriteria);
        if (!$this->machConfig->isEnabled()) {
            return $searchResults;
        }

        if (!count($searchResults->getItems())) {
            //our gift card wasn't found, create it, save it, and add to search results.
            $code = $this->_extractCode($searchCriteria);
            if (!$code) {
                return $searchResults;
            }

            $magentoGiftCard = $this->_createMagentoGiftCard($code);

            if ($magentoGiftCard) {
                $searchResults
                    ->setItems([$magentoGiftCard])
                    ->setTotalCount(1)
                    ->setSearchCriteria($searchCriteria);
            }

            return $searchResults;
        }

        foreach ($searchResults->getItems() as $magentoGiftCard) {
            /** @var Giftcardaccount $magentoGiftCard */
            $this->_updateMagentoGiftCard($magentoGiftCard);
            if ($magentoGiftCard->isDeleted()) {
                $searchResults->setTotalCount(0)
                    ->setItems([])
                    ->setSearchCriteria($searchCriteria);
            }
        }

        return $searchResults;
    }

    /**
     * @param Giftcardaccount $magentoGiftCard
     *
     * @return GiftCardAccountRepository
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _updateMagentoGiftCard(Giftcardaccount $magentoGiftCard): GiftCardAccountRepository
    {
        /** @var Giftcardaccount $magentoGiftCard */
        $machGiftCard = $this->giftCardInfo->get($magentoGiftCard->getCode());
        if (!$machGiftCard) {
            $this->delete($magentoGiftCard);
            return $this;
        }

        /** @var Giftcardaccount $giftCardAccount */
        $giftCardAccount = $this->mapGiftCards->fromMachGiftCardToMagentoGiftCard($machGiftCard);
        $giftCardAccount->setId($magentoGiftCard->getId());

        if ($this->_hashGiftCardAccount($magentoGiftCard) !== $this->_hashGiftCardAccount($giftCardAccount)) {
            $magentoGiftCard->setData($giftCardAccount->getData());
            //this will be used to suppress saving this event as a historical event.
            $magentoGiftCard->setData(self::DATA_KEY_GIFTCARD_UPDATED_BY_MACH, true);
            $this->save($magentoGiftCard);
        }

        return $this;
    }

    /**
     * @param string $code
     *
     * @return bool|Giftcardaccount
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _createMagentoGiftCard($code)
    {
        /** @var Giftcardaccount $magentoGiftCard */
        $machGiftCard = $this->giftCardInfo->get($code);
        if (!$machGiftCard) {
            return false;
        }

        $magentoGiftCard = $this->mapGiftCards->fromMachGiftCardToMagentoGiftCard($machGiftCard);
        $this->save($magentoGiftCard);

        return $magentoGiftCard;
    }

    /**
     * @param Giftcardaccount $giftcardAccount
     *
     * @return int
     */
    protected function _hashGiftCardAccount(Giftcardaccount $giftcardAccount): int
    {
        $data = [
            'code'          => $giftcardAccount->getCode(),
            'status'        => (int)$giftcardAccount->getStatus(),
            'state'         => (int)$giftcardAccount->getState(),
            'balance'       => (float)$giftcardAccount->getBalance(),
            'is_redeemable' => (int)$giftcardAccount->getIsRedeemable(),
            'date_expires'  => (string)$giftcardAccount->getDateExpires(),
        ];

        return crc32(serialize($data));
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return string|null
     */
    protected function _extractCode(SearchCriteriaInterface $searchCriteria): ?string
    {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            foreach ($group->getFilters() as $filter) {
                if ($filter->getField() === 'code') {
                    switch ($filter->getConditionType()) {
                        case 'eq':
                        case 'like':
                            return (string)$filter->getValue();
                        case 'in':
                            if (!is_array($filter->getValue())) {
                                return null;
                            }

                            /** @noinspection PhpWrongForeachArgumentTypeInspection */
                            foreach ($filter->getValue() as $code) {
                                return $code;
                            }

                            return null;
                    }

                    return $filter->getValue();
                }
            }
        }

        return null;
    }
}

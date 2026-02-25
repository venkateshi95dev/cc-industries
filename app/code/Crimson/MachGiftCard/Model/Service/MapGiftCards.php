<?php
/**
 * @namespace   Crimson
 * @module      MachGiftCard
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/8/2019 10:34 AM
 * @brief
 */

namespace Crimson\MachGiftCard\Model\Service;

use Crimson\MachGiftCard\Api\Data\MachGiftCardInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftCardAccount\Api\Data\GiftCardAccountInterfaceFactory;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MapGiftCards
 * @package Crimson\MachGiftCard\Model\Service
 */
class MapGiftCards
{
    protected $giftCardAccountFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MapGiftCards constructor.
     *
     * @param GiftCardAccountInterfaceFactory $giftCardAccountFactory
     * @param StoreManagerInterface                        $storeManager
     */
    public function __construct(
        GiftCardAccountInterfaceFactory $giftCardAccountFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->giftCardAccountFactory = $giftCardAccountFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param MachGiftCardInterface $machGiftCard
     *
     * @return Giftcardaccount
     * @throws NoSuchEntityException
     */
    public function fromMachGiftCardToMagentoGiftCard(MachGiftCardInterface $machGiftCard): Giftcardaccount
    {
        /** @var Giftcardaccount $giftCardAccount */
        $giftCardAccount = $this->giftCardAccountFactory->create();
        $giftCardAccount
            ->setIsRedeemable((int)($machGiftCard->getStatus() === Giftcardaccount::STATUS_ENABLED))
            ->setStatus($machGiftCard->getStatus())
            ->setState($machGiftCard->getState())
            ->setBalance((float) $machGiftCard->getBalance())
            ->setCode($machGiftCard->getCode())
            ->setDateCreated($machGiftCard->getIssueDate())
            ->setDateExpires($machGiftCard->getIssuedTo())
            ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
            ->setGiftCards([$machGiftCard->getCode()])
            ->setGiftCardsAmount((float) $machGiftCard->getAmount())
            ->setBaseGiftCardsAmount((float) $machGiftCard->getAmount())
            ->setGiftCardsAmountUsed((float) ($machGiftCard->getAmount() - $machGiftCard->getBalance()))
            ->setBaseGiftCardsAmountUsed((float) ($machGiftCard->getAmount() - $machGiftCard->getBalance()));

        return $giftCardAccount;
    }
}

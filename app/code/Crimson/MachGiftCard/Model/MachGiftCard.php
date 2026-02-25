<?php
/**
 * @namespace   Crimson
 * @module      MachGiftCard
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/7/2019 10:23 AM
 * @brief
 */

namespace Crimson\MachGiftCard\Model;

use Crimson\MachGiftCard\Api\Data\MachGiftCardInterface;
use Magento\Framework\DataObject;

/**
 * Class MachGiftCard
 * @package Crimson\MachGiftCard\Model
 */
class MachGiftCard extends DataObject implements MachGiftCardInterface
{
    /**
     * @param string|null $code
     * @return MachGiftCardInterface
     */
    public function setCode(?string $code): MachGiftCardInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->_getData(self::CODE);
    }

    /**
     * @param string|null $issueDate
     * @return MachGiftCardInterface
     */
    public function setIssueDate(?string $issueDate): MachGiftCardInterface
    {
        return $this->setData(self::ISSUE_DATE, $issueDate);
    }

    /**
     * @return string|null
     */
    public function getIssueDate(): ?string
    {
        return $this->_getData(self::ISSUE_DATE);
    }

    /**
     * @param string|null $issuedTo
     * @return MachGiftCardInterface
     */
    public function setIssuedTo(?string $issuedTo): MachGiftCardInterface
    {
        return $this->setData(self::ISSUED_TO, $issuedTo);
    }

    /**
     * @return string|null
     */
    public function getIssuedTo(): ?string
    {
        return $this->_getData(self::ISSUED_TO);
    }

    /**
     * @param float|null $amount
     * @return MachGiftCardInterface
     */
    public function setAmount(?float $amount): MachGiftCardInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->_getData(self::AMOUNT) ? (float) $this->_getData(self::AMOUNT) : null;
    }

    /**
     * @param int|null $status
     * @return MachGiftCardInterface
     */
    public function setStatus(?int $status): MachGiftCardInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->_getData(self::STATUS);
    }

    /**
     * @param int|null $state
     * @return MachGiftCardInterface
     */
    public function setState(?int $state): MachGiftCardInterface
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * @return int|null
     */
    public function getState(): ?int
    {
        return $this->_getData(self::STATE);
    }

    /**
     * @param float|null $openAmount
     * @return MachGiftCardInterface
     */
    public function setOpenAmount(?float $openAmount): MachGiftCardInterface
    {
        return $this->setData(self::OPEN_AMOUNT, $openAmount);
    }

    /**
     * @return float|null
     */
    public function getOpenAmount(): ?float
    {
        return $this->_getData(self::OPEN_AMOUNT) ? (float) $this->_getData(self::OPEN_AMOUNT) : null;
    }

    /**
     * @param float|null $balance
     * @return MachGiftCardInterface
     */
    public function setBalance(?float $balance): MachGiftCardInterface
    {
        return $this->setData(self::BALANCE, $balance);
    }

    /**
     * @return float|null
     */
    public function getBalance(): ?float
    {
        return $this->_getData(self::BALANCE) ? (float) $this->_getData(self::BALANCE) : null;
    }
}

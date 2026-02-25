<?php
/**
 * @namespace   Crimson
 * @module      MachGiftCard
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/7/2019 10:15 AM
 * @brief
 */

namespace Crimson\MachGiftCard\Api\Data;

/**
 * Interface MachGiftCardInterface
 * @package Crimson\MachGiftCard\Api\Data
 */
interface MachGiftCardInterface
{
    const CODE = 'code';
    const ISSUE_DATE = 'issue_date';
    const ISSUED_TO = 'issued_to';
    const AMOUNT = 'amount';
    const STATUS = 'status';
    const STATE = 'state';
    const OPEN_AMOUNT = 'open_amount';
    const BALANCE = 'balance';

    /**
     * @param string|null $code
     *
     * @return MachGiftCardInterface
     */
    public function setCode(?string $code): MachGiftCardInterface;

    /**
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * @param string|null $issueDate
     *
     * @return MachGiftCardInterface
     */
    public function setIssueDate(?string $issueDate): MachGiftCardInterface;

    /**
     * @return string|null
     */
    public function getIssueDate(): ?string;

    /**
     * @param string|null $issuedTo
     *
     * @return MachGiftCardInterface
     */
    public function setIssuedTo(?string $issuedTo): MachGiftCardInterface;

    /**
     * @return string|null
     */
    public function getIssuedTo(): ?string;

    /**
     * @param float|null $amount
     *
     * @return MachGiftCardInterface
     */
    public function setAmount(?float $amount): MachGiftCardInterface;

    /**
     * @return float|null
     */
    public function getAmount(): ?float;

    /**
     * @param int|null $status
     *
     * @return MachGiftCardInterface
     */
    public function setStatus(?int $status): MachGiftCardInterface;

    /**
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * @param int|null $state
     *
     * @return MachGiftCardInterface
     */
    public function setState(?int $sttate): MachGiftCardInterface;

    /**
     * @return int|null
     */
    public function getState(): ?int;

    /**
     * @param float|null $openAmount
     *
     * @return MachGiftCardInterface
     */
    public function setOpenAmount(?float $openAmount): MachGiftCardInterface;

    /**
     * @return float|null
     */
    public function getOpenAmount(): ?float;

    /**
     * @param float|null $balance
     *
     * @return MachGiftCardInterface
     */
    public function setBalance(?float $balance): MachGiftCardInterface;

    /**
     * @return float|null
     */
    public function getBalance(): ?float;
}

<?php

declare(strict_types=1);

namespace Crimson\AmastyStorePickupWithLocator\Model\Queue\Email\Dto;

class EmailConfig
{
    /**
     * @var string|null
     */
    private ?string $sender = null;

    /**
     * @var string|null
     */
    private ?string $template = null;

    /**
     * @var int|null
     */
    private ?int $storeId = null;

    /**
     * @var string[]
     */
    private array $recipients = [];

    /**
     * @var string|null
     */
    private ?string $jsonData = null;

    /**
     * @return string|null
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * @param string|null $sender
     * @return void
     */
    public function setSender(?string $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     * @return void
     */
    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return $this->storeId ? (int)$this->storeId : null;
    }

    /**
     * @param int|null $storeId
     * @return void
     */
    public function setStoreId(?int $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * @param string[] $recipients
     * @return void
     */
    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    /**
     * @return string|null
     */
    public function getJsonData(): ?string
    {
        return $this->jsonData;
    }

    /**
     * @param string|null $jsonData
     * @return void
     */
    public function setJsonData(?string $jsonData): void
    {
        $this->jsonData = $jsonData;
    }

    /**
     * @return void
     */
    public function _resetState(): void
    {
        $this->sender = null;
        $this->template = null;
        $this->storeId = null;
        $this->recipients = [];
        $this->jsonData = null;
    }
}

<?php
/**
 * @namespace   Crimson
 * @module      MachBase
 * @author      Matheus Gontijo
 * @email       mgontijo@crimsonagility.com
 * @date        12/20/2018 3:12 PM
 * @brief
 */

namespace Crimson\MachBase\Model\Api;

use Crimson\MachBase\Model\Logger\BuildExceptionMessage;
use Crimson\MachBase\Model\Service\GetMachSku;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Crimson\MachBase\Model\Log;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Crimson\MachBase\Helper\Data;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ApiContext
 * @package Crimson\MachBase\Model\Api
 */
class ApiContext
{
    /**
     * @var BuildExceptionMessage
     */
    protected $buildExceptionMessage;
    /**
     * @var CacheInterface
     */
    protected $cacheManager;
    /** @var Client|null */
    protected $client;
    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /** @var ScopeConfigInterface|null */
    protected $config = null;
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var GetMachSku
     */
    protected $getMachSku;
    /**
     * @var Json
     */
    protected $json;

    /** @var Log|null */
    protected $log = null;

    /** @var EncryptorInterface|null */
    protected $encryptor = null;

    /** @var Data|null $helper */
    protected $helper = null;

    public function __construct(
        ClientFactory                         $clientFactory,
        ScopeConfigInterface                  $config,
        EncryptorInterface                    $encryptor,
        Log                                   $log,
        Data                                  $helper,
        BuildExceptionMessage                 $buildExceptionMessage,
        public readonly StoreManagerInterface $storeManager,
        GetMachSku                            $getMachSku,
        CacheInterface                        $cacheManager,
        Json                                  $json,
        EventManager                          $eventManager
    ) {
        $this->clientFactory         = $clientFactory;
        $this->config                = $config;
        $this->encryptor             = $encryptor;
        $this->log                   = $log;
        $this->helper                = $helper;
        $this->cacheManager          = $cacheManager;
        $this->json                  = $json;
        $this->buildExceptionMessage = $buildExceptionMessage;
        $this->getMachSku = $getMachSku;
        $this->eventManager = $eventManager;
    }

    public function getClient()
    {
        if(!$this->client){
            $this->client = $this->clientFactory->create();
        }

        return $this->client;
    }

    /**
     * @param $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return ScopeConfigInterface|null
     */
    public function getConfig(): ?ScopeConfigInterface
    {
        return $this->config;
    }

    /**
     * @return EncryptorInterface|null
     */
    public function getEncryptor(): ?EncryptorInterface
    {
        return $this->encryptor;
    }

    /**
     * @return Log|null
     */
    public function getLog(): ?Log
    {
        return $this->log;
    }

    /**
     * @return Data|null
     */
    public function getHelper(): ?Data
    {
        return $this->helper;
    }

    /**
     * @return GetMachSku
     */
    public function getMachSku(): GetMachSku
    {
        return $this->getMachSku;
    }

    /**
     * @param \Exception $exception
     *
     * @return string
     */
    public function buildExceptionMessage(\Exception $exception): string
    {
        return $this->buildExceptionMessage->build($exception);
    }

    /**
     * @return CacheInterface
     */
    public function getCacheManager(): CacheInterface
    {
        return $this->cacheManager;
    }

    /**
     * @return Json
     */
    public function getJson(): Json
    {
        return $this->json;
    }

    /**
     * @return EventManager
     */
    public function getEventManager(): EventManager
    {
        return $this->eventManager;
    }
}

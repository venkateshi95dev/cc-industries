<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\MessageQueue\Console;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;

class CronBase extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CronBase constructor.
     *
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param File $file
     * @param Curl $curl
     * @param ScopeConfigInterface $scopeConfig
     * @param string $name
     */
    public function __construct(
        State $state,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        File $file,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        $name = null
    ) {
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->file = $file;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($name);
    }

    /**
     * Execute cron
     *
     * @param string $cronFileName
     * @param string $syncUrl
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function executeCron($cronFileName, $syncUrl)
    {
        $storeScope = ScopeInterface::SCOPE_WEBSITE;
        $token = $this->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_credentials/token',
            $storeScope,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
        $magentoVersion = $this->productMetadata->getVersion();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $this->curl->setHeaders(['Content-Type' => 'application/json',
            'charset' => 'utf-8',
            'Authorization' => 'Bearer ' . $token]);
        $this->curl->setOption(CURLOPT_POST, 1);
        $this->curl->setOption(CURLOPT_POSTFIELDS, []);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        if (version_compare($magentoVersion, '2.4', '>=')) {
            $baseUrl = str_replace($cronFileName, "", $baseUrl);
        }
        $finalUrl = $baseUrl . $syncUrl;
        $this->curl->post($finalUrl, []);
        return $this->curl->getBody();
    }

    /**
     * Write cron output
     *
     * @param string $message
     * @param string $response
     */
    public function writeCronOutput($message, $response = null)
    {
        $dirPath = __DIR__;
        $dirpath = str_replace("app/code/I95DevConnect/MessageQueue/Console", "", $dirPath);
        $dateFormat = 'Y-m-d H:i:s';
        $filename1 = $dirpath . "/var/log/Cronruning.log";
        $fp = $this->file->fileOpen($filename1, "a");
        $this->file->fileWrite($fp, "Cron Output " . json_encode($response) . "\n");
        $this->file->fileWrite($fp, $message . date($dateFormat) . "\n");
        $this->file->fileClose($fp);
    }
}

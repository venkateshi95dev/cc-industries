<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

// phpcs:disable
$dirPath = __DIR__;
$dirpath = str_replace("app/code/I95DevConnect/CloudConnect", "", $dirPath);

require $dirpath . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);

try {
    $obj = $bootstrap->getObjectManager();
    $appState = $obj->get("\Magento\Framework\App\State");
    $appState->setAreaCode("global");

    $storeManager = $obj->get("\Magento\Store\Model\StoreManagerInterface");
    $productMetadata = $obj->get("\Magento\Framework\App\ProductMetadataInterface");
    $magentoVersion = $productMetadata->getVersion();
    $baseUrl = $storeManager->getStore()->getBaseUrl();


    $fileSystem = $obj->create("\Magento\Framework\Filesystem\Driver\File");
    $filename1 = $dirpath . "/var/log/Cronruning.log";
    $fp = $fileSystem->fileOpen($filename1, "a");
    $dateFormat = 'Y-m-d H:i:s';
    $fileSystem->fileWrite($fp, "Cron Started " . date($dateFormat) . "\n");
    $fileSystem->fileClose($fp);

    $storeScope = ScopeInterface::SCOPE_WEBSITE;
    $storeManager = $obj->get(StoreManagerInterface::class);
    $scopeConfig = $obj->get(ScopeConfigInterface::class);
    $token = $scopeConfig->getValue(
        'i95dev_messagequeue/I95DevConnect_credentials/token',
        $storeScope,
        $storeManager->getDefaultStoreView()->getWebsiteId()
    );

} catch (LocalizedException $ex) {
    $fp = $fileSystem->fileOpen($filename1, "a");
    $fileSystem->fileWrite($fp, $ex->getMessage() . "\n");
    $fileSystem->fileWrite($fp, "Cron End " . date($dateFormat) . "\n");
    $fileSystem->fileClose($fp);
}

/**
 * @param $obj
 * @param $token
 * @param $magentoVersion
 * @param $baseUrl
 * @param $cronFileName
 * @param $syncUrl
 * @return mixed
 */
function executeCron($obj, $token, $magentoVersion, $baseUrl, $cronFileName, $syncUrl)
{
    $curl = $obj->get(Curl::class);
    $curl->setHeaders(['Content-Type' => 'application/json',
        'charset' => 'utf-8',
        'Authorization' => 'Bearer ' . $token]);
    $curl->setOption(CURLOPT_POST, 1);
    $curl->setOption(CURLOPT_POSTFIELDS, []);
    $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
    if (version_compare($magentoVersion, '2.4', '>=')) {
        $baseUrl = str_replace($cronFileName, "", $baseUrl);
    }
    $finalUrl = $baseUrl . $syncUrl;
    $curl->post($finalUrl, []);
    return $curl->getBody();
}

/**
 * @param $fileSystem
 * @param $filename1
 * @param $response
 * @param $dateFormat
 */
function writeCronOutput($fileSystem, $filename1, $response, $dateFormat)
{
    $fp = $fileSystem->fileOpen($filename1, "a");
    $fileSystem->fileWrite($fp, "Cron Output " . json_encode($response) . "\n");
    $fileSystem->fileWrite($fp, "Cron End " . date($dateFormat) . "\n");
    $fileSystem->fileClose($fp);

    echo json_encode($response);
}
// phpcs:enable
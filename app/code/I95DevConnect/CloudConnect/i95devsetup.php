<?php

// phpcs:disable
DEFINE('DS', DIRECTORY_SEPARATOR);
DEFINE('SI', '\Magento\Store\Model\StoreManagerInterface');
DEFINE('FC', "\Magento\Framework\Filesystem\Driver\File");
DEFINE('DL', '\Magento\Framework\App\Filesystem\DirectoryList');
DEFINE('I95DEV', "I95DevConnect");
DEFINE('CRONSTRING', '(crontab -l | grep -v  " ');
DEFINE('CRONTIME', '(crontab -l; echo "*/1 * * * * sh ');

use Magento\Framework\App\Area;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

$dir_name = preg_replace('#\/[^/]*$#', '', dirname(__FILE__));

$dirPath = __DIR__;
$dirPath = str_replace("app/code/I95DevConnect/CloudConnect", "", $dirPath);

require $dirPath . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$storeManager = $objectManager->get(SI);
$logString = "";
try {
    $fileSystem = $objectManager->get(FC);
    $log_file = $dirPath . "/var/log/ConnectorSetup.log";


    $directoryList = $objectManager->get(DL);
    $filePath = $directoryList->getRoot();
    $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
    $version = $productMetadata->getVersion();
    $statiContent = 'php ' . $dirPath . '/bin/magento setup:static-content:deploy';

    $sourcePath1 = $filePath . DS . 'generated';
    $sourcePath2 = $filePath . DS . 'pub' . DS . 'static';
    $sourcePath3 = $filePath . DS . 'var';
    if (!is_writable_r($sourcePath1) || !is_writable_r($sourcePath2) || !is_writable_r($sourcePath3)) {
        $logString .= setPermission($dirPath);
        if (!is_writable_r($sourcePath1) || !is_writable_r($sourcePath2) || !is_writable_r($sourcePath3)) {
            $logString .= "ERROR : As instructed you need to login from a user with permission to provide write access to var, generated and pub/static folder";
            logMsg($fileSystem, $log_file, $logString);
            exit();
        }
    }

    if (version_compare($version, '2.2.0', '>=')) {
        $statiContent .= ' -f';
    }
    // Installation process started
    $logString .= "Installation started...\n";
    $appState = $objectManager->get("Magento\Framework\App\State");
    $appState->setAreaCode(Area::AREA_GLOBAL);

    $directoryList = $objectManager->get(DL);
    $filePath = $directoryList->getRoot();

    $configModel = $objectManager->create('\Magento\Config\Model\ResourceModel\Config');
    if (strpos($appState->getMode(), 'production') !== false) {
        $configModel->saveConfig('dev/static/sign', 0, 'default', 0);
    }

    $scope_website = 'default';

    $logString .= "Started setting default i95devconnector magento configurations value...\n";
    $configModel = $objectManager->get('\Magento\Config\Model\ResourceModel\Config');
    $baseExtension = 'i95dev_messagequeue/i95dev_extns/enabled';
    $component = 'i95dev_messagequeue/I95DevConnect_settings/component';
    $captureInvoice = 'i95dev_messagequeue/I95DevConnect_settings/capture_invoice';
    $emailNotifications = 'i95dev_messagequeue/I95DevConnect_notifications/email_notifications';
    $orderTotalmismatch = 'i95dev_messagequeue/I95DevConnect_notifications/order_totalmismatch';
    $retryLimit = 'i95dev_messagequeue/I95DevConnect_mqsettings/retry_limit';
    $customerGroup = 'i95dev_messagequeue/I95DevConnect_settings/customer_group';
    $attributeSet = 'i95dev_messagequeue/I95DevConnect_settings/attribute_set';
    $attributeGroup = 'i95dev_messagequeue/I95DevConnect_settings/attribute_group';
    $packetSize = 'i95dev_messagequeue/i95dev_extns/packet_size';
    $priceLevelEnable = 'i95dev_pricelevel/active_display/enabled';
    $configModel->saveConfig($baseExtension, 1, $scope_website);
    $configModel->saveConfig($component, 'BC', $scope_website);
    $configModel->saveConfig($captureInvoice, 0, $scope_website);
    $configModel->saveConfig($emailNotifications, 'invoice,shipment', $scope_website);
    $configModel->saveConfig($orderTotalmismatch, 1, $scope_website);
    $configModel->saveConfig($retryLimit, 5, $scope_website);
    $configModel->saveConfig($customerGroup, 1, $scope_website);
    $configModel->saveConfig($attributeSet, 4, $scope_website);
    $configModel->saveConfig($attributeGroup, 7, $scope_website);
    $configModel->saveConfig($packetSize, 50, $scope_website);
    $configModel->saveConfig($priceLevelEnable, 1, $scope_website);
    $logString .= "Completed setting default i95devconnector magento configurations value...\n";

    $logString .= "Started setting default i95devconnector email configurations value...\n";
    $contactEmailPath = 'i95dev_messagequeue/I95DevConnect_generalcontact/email_sent';
    $contactUserPath = 'i95dev_messagequeue/I95DevConnect_generalcontact/username';
    $contactEmail = 'trans_email/ident_general/email';
    $contactName = 'trans_email/ident_general/name';
    $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
    $email = $scopeConfig->getValue($contactEmail, ScopeInterface::SCOPE_STORE);
    $name = $scopeConfig->getValue($contactName, ScopeInterface::SCOPE_STORE);
    $configModel->saveConfig($contactEmailPath, $email, $scope_website);
    $configModel->saveConfig($contactUserPath, $name, $scope_website);
    $logString .= "Completed default i95devconnector email configurations settings...\n";

    $logString .= "Started setting default i95devconnector log configurations value...\n";
    $maxLogSize = 'i95dev_messagequeue/I95DevConnect_logsettings/max_log_size';
    $logCleanDays = 'i95dev_messagequeue/I95DevConnect_logsettings/log_clean_days';
    $debug = 'i95dev_messagequeue/I95DevConnect_logsettings/debug';
    $configModel->saveConfig($maxLogSize, 5000, $scope_website);
    $configModel->saveConfig($logCleanDays, 7, $scope_website);
    $configModel->saveConfig($debug, 1, $scope_website);
    $logString .= "Completed setting default i95devconnector email configurations value...\n";

    $logString .= "Started setting default i95devconnector log configurations value...\n";
    $enablevariants = 'configurableproducts/i95dev_enabled_settings/is_enabled';
    $configModel->saveConfig($enablevariants, 1, $scope_website);
    $logString .= "Started setting default i95devconnector configurable/variant configurations value...\n";

    $logString .= "Started setting i95devconnector cloud configurations value...\n";
    $genericenablePath = 'i95dev_adapter_configurations/enabled_disabled/enabled';
    $clientIdPath = 'i95dev_adapter_configurations/enabled_disabled/client_id';
    $subscriptionPath = 'i95dev_adapter_configurations/enabled_disabled/subscription_key';
    $endpointcodePath = 'i95dev_adapter_configurations/enabled_disabled/endpoint_code';
    $apiAuthenticationTokenPath = 'i95dev_adapter_configurations/enabled_disabled/token';
    $logsPath = 'i95dev_adapter_configurations/enabled_disabled/logs_enabled';
    $webservicePath = 'i95dev_adapter_configurations/enabled_disabled/target_url';
    $instanceTypePath = 'i95dev_adapter_configurations/enabled_disabled/instance_type';
    $paymentEnable = 'i95dev_adapter_configurations/i95dev_payment_mapping/enabled';
    $shippingEnable = 'i95dev_adapter_configurations/i95dev_shipping_mapping/enabled';
    $cloudWebserviceURL = '';
    $subscriptionKey = '';
    $clientId = '';
    $instanceType = '';
    $endpointcode = '';

    $logString .= $filename = $filePath . DS . "app" . DS . "code" . DS . I95DEV . DS . "CloudConnect" . DS . "LicenseInfo.txt";

    $driver = $objectManager->get(FC);
    if ($driver->isExists($filename)) {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $list = explode("|", $line);
            if (isset($list[0]) && isset($list[1]) && !empty($list[0]) && !empty($list[1])) {
                $licensekey = strtolower(str_replace(' ', '', $list[0]));
                $value = str_replace(' ', '', $list[1]);
                switch ($licensekey) {
                    case "customerid":
                        $clientId = $value;
                        break;
                    case "subscriptionkey":
                        $subscriptionKey = $value;
                        break;
                    case "serviceurl":
                        $cloudWebserviceURL = $value;
                        break;
                    case "instancetype":
                        $instanceType = $value;
                        break;
                    case "endpointcode":
                        $endpointcode = $value;
                        break;
                    case 'token':
                        $apiAuthenticationToken = $value;
                        break;
                    default:
                        break;
                }
            }
        }
    } else {
        $logString .= "ERROR : License file does not exist, you need to setup configuration manually..\n";
        logMsg($fileSystem, $log_file, $logString);
        exit();
    }

    $configModel->saveConfig($genericenablePath, 1, $scope_website);
    $configModel->saveConfig($webservicePath, $cloudWebserviceURL, $scope_website);
    $configModel->saveConfig($clientIdPath, $clientId, $scope_website);
    $configModel->saveConfig($subscriptionPath, $subscriptionKey, $scope_website);
    $configModel->saveConfig($endpointcodePath, $endpointcode, $scope_website);
    $configModel->saveConfig($apiAuthenticationTokenPath, $apiAuthenticationToken, $scope_website);
    $configModel->saveConfig($instanceTypePath, $instanceType, $scope_website);
    $configModel->saveConfig($logsPath, 1, $scope_website);
    $configModel->saveConfig($paymentEnable, 1, $scope_website);
    $configModel->saveConfig($shippingEnable, 1, $scope_website);

    $logString .= "Completed setting of i95devconnector cloud configurations value...\n";

    $logString .= "Started setting of i95devconnector cancel order configurations value...\n";
    $enabelCancelOrder = "i95devconnect_CancelOrder/cancelorder_enabled_settings/enable_cancelorder";
    $configModel->saveConfig($enabelCancelOrder, 1, $scope_website);
    $logString .= "Completed setting of i95devconnector cancel order configurations value...\n";

    $logString .= "Started setting of i95devconnector tax configurations value...\n";
    $enableTax = "i95devconnect_vattax/vattax_enabled_settings/enable_vattax";
    $configModel->saveConfig($enableTax, 1, $scope_website);
    $logString .= "Completed setting of i95devconnector tax configurations value...\n";

    $logString .= "Started setting of i95devconnector Error Data configurations value...\n";
    $enableErrorData = "i95devconnect_errors/reports_enabled_settings/report";
    $configModel->saveConfig($enableErrorData, 1, $scope_website);
    $logString .= "Completed setting of i95devconnector Error Data configurations value...\n";

    $logString .= "Removing the cron job\n";
    $logString .= $output1 = setCronJob(CRONSTRING . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPullDataCron.sh") | crontab -');
    $logString .= $output2 = setCronJob(CRONSTRING . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPushDataCron.sh") | crontab -');
    $logString .= $output3 = setCronJob(CRONSTRING . $filePath . '/app/code/I95DevConnect/MessageQueue/i95devCron.sh") | crontab -');
    $logString .= $output4 = setCronJob(CRONSTRING . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPushResponseCron.sh") | crontab -');
    $logString .= $output5 = setCronJob(CRONSTRING . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPullResponseCron.sh") | crontab -');
    $logString .= "Cron job removed\n";

    $logString .= "Setting up cron job for you...\n";
    $logString .= $output1 = setCronJob(CRONTIME . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPullDataCron.sh") | crontab -');
    $logString .= $output2 = setCronJob(CRONTIME . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPushDataCron.sh") | crontab -');
    $logString .= $output3 = setCronJob(CRONTIME . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPushResponseCron.sh") | crontab -');
    $logString .= $output4 = setCronJob(CRONTIME . $filePath . '/app/code/I95DevConnect/CloudConnect/i95devPullResponseCron.sh") | crontab -');
    $logString .= $output5 = setCronJob(CRONTIME . $filePath . '/app/code/I95DevConnect/MessageQueue/i95devCron.sh") | crontab -');

    $logString .= cacheClean($dirPath);
    $logString .= setPermission($dirPath);
    $directoryList = $objectManager->get(DL);
    $filePath = $directoryList->getRoot();
    $sourcePath = $filePath . DS . 'i95Dev' . DS . I95DEV . DS . 'ShippingMapping';
    $driverModel = $objectManager->create(FC);
    if ($driverModel->isExists($sourcePath)) {
        $logString .= "Start sending shipping mapping default method list to cloud\n";
        $storeManager = $objectManager->get(SI);
        $url = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $sendShippingMethodUrl = $url . 'cloudconnect/get/index';

        $cSession = curl_init();
        curl_setopt($cSession, CURLOPT_URL, $sendShippingMethodUrl);
        curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cSession, CURLOPT_HEADER, false);
        $result=curl_exec($cSession);
        curl_close($cSession);
        $logString .= "Shipping mapping default method list to cloud completed\n";

        $logString .= "Start sending shipping mapping default data to cloud\n";
        $sendShippingMappingUrl = $url . 'cloudconnect/shipping/sendMapping';
        $cSession = curl_init();
        curl_setopt($cSession, CURLOPT_URL, $sendShippingMappingUrl);
        curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cSession, CURLOPT_HEADER, false);
        $result=curl_exec($cSession);
        curl_close($cSession);
        $logString .= "Shipping mapping default data to cloud completed\n";
    }

    $sourcePath = $filePath . DS . 'i95Dev' . DS . I95DEV . DS . 'PaymentMapping';
    $driverModel = $objectManager->create(FC);
    if ($driverModel->isExists($sourcePath)) {
        $logString .= "Start sending payment mapping default method list to cloud\n";
        $storeManager = $objectManager->get(SI);
        $url = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $sendPaymentMethodUrl = $url . 'cloudconnect/payment/index';

        $cSession = curl_init();
        curl_setopt($cSession, CURLOPT_URL, $sendPaymentMethodUrl);
        curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cSession, CURLOPT_HEADER, false);
        $result=curl_exec($cSession);
        curl_close($cSession);
        $logString .= "Payment mapping default method list to cloud completed\n";

        $logString .= "Start sending payment mapping default data to cloud\n";
        $sendPaymentMappingUrl = $url . 'cloudconnect/payment/sendMapping';
        $cSession = curl_init();
        curl_setopt($cSession, CURLOPT_URL, $sendPaymentMappingUrl);
        curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cSession, CURLOPT_HEADER, false);
        $logString .= $result=curl_exec($cSession);
        curl_close($cSession);
        $logString .= "Payment mapping default data to cloud completed\n";
    }

    $logString .= cacheClean($dirPath);
    $logString .= setPermission($dirPath);
    logMsg($fileSystem, $log_file, $logString);
} catch (LocalizedException $e) {
    $logString .= $e->getMessage();
    logMsg($fileSystem, $log_file, $logString);
}

/**
 * @param $cronString
 * @return string|null
 */
function setCronJob($cronString)
{
    return shell_exec($cronString);
}

/**
 * @param $dir_name
 * @return string
 */
function cacheClean($dir_name)
{
    $logString = "Clearing the Cache...\n";
    $logString .= $cacheClear = 'php ' . $dir_name . '/bin/magento cache:clean && php ' . $dir_name . '/bin/magento cache:flush';
    $logString .= "\n";
    $logString .= shell_exec($cacheClear);
    $logString .= "\n";
    return $logString . "Cache clear completed...\n";
}

/**
 * @param $dir_name
 * @return string
 */
function setPermission($dir_name)
{
    $logString = "Setting permissions \n";
    $output = shell_exec('chmod 777 -R ' . $dir_name . '/generated ' . $dir_name . '/var '. $dir_name . '/pub' . DIRECTORY_SEPARATOR . 'static');
    $logString .= $output;
    if (!empty($output)) {
        $logString .= "\n Some issue occured while setting permissions. Please set required permission to generated, pub/static, and var folders manually \n";
    } else {
        $logString .= "\nPermission  setting completed\n";
    }

    return $logString;
}

/**
 * @param $fileSystem
 * @param $path
 * @param $msg
 */
function logMsg($fileSystem, $path, $msg)
{
    $fp = $fileSystem->fileOpen($path, "a");
    $fileSystem->fileWrite($fp, $msg . "\n");
    $fileSystem->fileClose($fp);
}

/**
 * @param $dir
 * @return bool
 */
function is_writable_r($dir)
{
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != ".." && !(is_writable_r($dir . "/" . $object))) {
                        return false;
                }
            }
            $return_val = true;
        } else {
            $return_val = false;
        }
    } elseif (file_exists($dir)) {
        $return_val =  (is_writable($dir));
    }

    return $return_val;
}
// phpcs:enable

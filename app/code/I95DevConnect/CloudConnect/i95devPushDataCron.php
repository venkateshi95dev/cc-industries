<?php

// phpcs:disable
include_once "cronBase.php";

$response = executeCron(
    $obj,
    $token,
    $magentoVersion,
    $baseUrl,
    "i95devPushDataCron.php/",
    "rest/corvette_central_store/V1/CloudConnect/PushData/?methodName=syncData"
);

writeCronOutput($fileSystem, $filename1, $response, $dateFormat);
// phpcs:enable
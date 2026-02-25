<?php

namespace Silk\Coker\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;

class Getpaymentinfo extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    protected $paymentHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

        /**
     * @var LoggerInterface
     */
    protected $logger;
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Silk\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->logger = $logger;
        $this->paymentHelper = $paymentHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
      // $post = array(
      //   'hostname' => 'cert.api.firstdata.com',
      //   'credentials' =>
      //     array(
      //       'apiKey' => 'raxhI7YY4W9AdO52wGzpxHAL18GBRhcA',
      //       'apiSecret' => 'rMfxQiXsxkE2zVy1'
      //     ),
      //   'gatewayConfig' =>
      //     array(
      //       'gateway' => 'PAYEEZY',
      //       'apiKey' => 'raxhI7YY4W9AdO52wGzpxHAL18GBRhcA',
      //       'apiSecret' => '3d20674cc65f70be282c49975509844ef5d901a73631b665a3fdb17626303963',
      //       'authToken' => 'fdoa-1f86f1c79bcb38dc1651e53eb54a7fa79da12c02cb6e38f7',
      //       'transarmorToken' => 'NOIW',
      //       'zeroDollarAuth' => false
      //     )
      // );
        $nonce = strtotime(gmdate("Y-m-d H:i:s", time())) * 1000 + rand();
        $timestamp = strtotime(gmdate("Y-m-d H:i:s", time())) * 1000;
        $apiKey = $this->paymentHelper->getCredentialsConfig('apikey');
        $secretKey = $this->paymentHelper->getCredentialsConfig('apisecret');
        $contentType = 'application/json';

        $data = [
          'gateway' => $this->paymentHelper->getGatewayConfig('gateway'),
          'apiKey' => $this->paymentHelper->getGatewayConfig('apikey'),
          'apiSecret' => $this->paymentHelper->getGatewayConfig('apisecret'),
          'authToken' => $this->paymentHelper->getGatewayConfig('token'),
          'transarmorToken' => $this->paymentHelper->getGatewayConfig('transarmortoken'),
          'zeroDollarAuth' => (boolean)$this->paymentHelper->getGatewayConfig('zeroDollarAuth')
        ];
        $url = $this->paymentHelper->getApiUrl();
        $jsonPayload = json_encode($data);

        $msg = $apiKey . $nonce . $timestamp . $jsonPayload;
        // $messageSignature = base64_encode(hash_hmac('sha256', $msg, $secretKey));

        $messageSignature = $this->paymentHelper->genHmac($msg, $secretKey);

        $headers = array(
          'Api-Key: ' . $apiKey,
          'Content-Type: ' . $contentType,
          'Content-Length: ' . strlen($jsonPayload),
          'Message-Signature: ' . $messageSignature,
          'Nonce: ' . $nonce,
          'Timestamp: ' . $timestamp
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/authorize.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);
        $this->logger->info('response', [$response]);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $header = [];
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rows = explode("\r\n", trim(substr($response, 0, $header_size)));

        foreach ($rows as $key => $row) {
            if (preg_match('/(.*?): (.*)/', $row, $matches)) {
                $header[$matches[1]] = $matches[2];
            }
        }
        $data = [];

        if (isset($header['Client-Token'])) {
            $client_token = $header['Client-Token'];
        }
        if (isset($header['Nonce'])) {
            $responseNonce = $header['Nonce'];
        }
        $body = substr($response, $header_size);
        $publicKeyBase64 = substr($body, 20, -2);

        if ($http_status === 200) {
            if ($responseNonce == $nonce) {
                $data = ['clientToken'=> $client_token, 'publicKeyBase64' => $publicKeyBase64];
                $this->logger->info('response data', [$data]);
                header('Content-Type: application/json');
                $resp =  json_encode($data);
                echo $resp;
            } else {
                header('Content-Type: application/json');
                $resp =  json_encode($data);
                echo $resp;
                // throw new Exception('nonce validation failed for nonce "' + $nonce + '"', 1);
            }
        } else {
              header('Content-Type: application/json');
              $resp =  json_encode($data);
              echo $resp;
              //throw new Exception('received HTTP ' + $http_status, 1);
        };
    }
}

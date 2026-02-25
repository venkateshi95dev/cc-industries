<?php

namespace Crimson\MachBase\Model\Api;

use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Addressclient
 * @package Crimson\MachBase\Model\Api
 */
class Addressclient extends \SoapClient
{

    public function __construct(
        protected ScopeConfigInterface $config,
        protected LoggerInterface $logger,
        protected MachConfig $machConfig
    ) {
        $originalValue = ini_get('default_socket_timeout');

        $wsdl    = $this->machConfig->getWsdlAddressUrl();
        $timeout = $this->machConfig->getSoapTimeout();

        ini_set('default_socket_timeout', $timeout);

        $options = [
            'trace'              => 1,
            'exceptions'         => 1,
            'connection_timeout' => $timeout,
        ];

        $options = array_merge([
            'cache_wsdl'     => $this->machConfig->getCacheWsdl(),
            'stream_context' => stream_context_create(array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                )
            ))
        ], $options);

        try {
            parent::__construct($wsdl, $options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        ini_set('default_socket_timeout', $originalValue);
    }
}

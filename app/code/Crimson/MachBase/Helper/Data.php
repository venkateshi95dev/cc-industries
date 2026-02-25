<?php

namespace Crimson\MachBase\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class Data
 * @package Crimson\MachBase\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var SubscriberFactory $subscriberFactory
     */
    private $_subscriberFactory;

    /**
     * @var TimezoneInterface $localeDate
     */
    private $_localeDate;

    /**
     * @var DateTime $coreDate
     */
    private $_coreDate;

    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        TimezoneInterface $localeDate,
        DateTime $coreDate
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->_localeDate = $localeDate;
        $this->_coreDate = $coreDate;
        parent::__construct($context);
    }

    /**
     * @param $input
     * @return bool
     */
    public function isNewsletterSubscribed($input): bool
    {
        if ($input instanceof \Magento\Framework\DataObject) {
            $email = $input->getEmail();
            if ($email) {
                return $this->isEmailNewsletterSubscribed($email);
            }

            $email = $input->getCustomerEmail();
            if ($email) {
                return $this->isEmailNewsletterSubscribed($email);
            }
        } elseif (is_string($input)) {
            return $this->isEmailNewsletterSubscribed($input);
        }

        return false;
    }

    /**
     * @param $email
     * @return bool
     */
    public function isEmailNewsletterSubscribed($email): bool
    {
        /** @var \Magento\Newsletter\Model\Subscriber $resource */
        $resource = $this->_subscriberFactory->create();
        $result   = $resource->loadByEmail($email);

        if (empty($result)
            || !isset($result[$resource->getIdFieldName()])
            || !isset($result['subscriber_status'])
        ) {
            return false;
        }

        $subscribed = ($result['subscriber_status'] === \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED);

        return ($result[$resource->getIdFieldName()] && $subscribed);
    }

    /**
     * @param $datetime
     * @return \DateTime
     * @throws \Exception
     */
    public function getIntegrationDate($datetime): \DateTime
    {
        $date = $this->_localeDate->date(new \DateTime($datetime), null, false);

        $timeZone = new \DateTimeZone($this->scopeConfig->getValue('mach/settings/integration_timezone'));

        $date->setTimezone($timeZone);

        return $date;
    }

    /**
     * @param $datetime
     * @return \DateTime
     * @throws \Exception
     */
    public function getAuthDate($datetime): \DateTime
    {
        $date = $this->_localeDate->date(new \DateTime($datetime), null, false);

        return $date;
    }

    /**
     * @param \Exception $e
     * @return string
     */
    public function buildExceptionMessage(\Exception $e): string
    {
        $message = 'Message: ' . $e->getMessage() . '<br/>' . PHP_EOL;
        $message .= 'File: ' . $e->getFile() . '<br/>' . PHP_EOL;
        $message .= 'Line: ' . $e->getLine() . '<br/>' . PHP_EOL . '<br/>' . PHP_EOL;
        $message .= $e->getTraceAsString();

        return $message;
    }

    /**
     * @param null $action
     * @return int
     */
    public function getMachErpErrorThreshold($action = null): int
    {
        return 5;
    }

    /**
     * @return string
     */
    public function getGmtDate(): string
    {
        return $this->_coreDate->gmtDate();
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function getSalesOrderUrl($route, $params = []): string
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function buildUrl($route, $params = []): string
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * @param $olderDate
     * @param null $youngerDate
     * @return float
     */
    public function calculateDateDiffInMinutes($olderDate, $youngerDate = null): float
    {
        try {
            $olderDate = new \Zend_Date($olderDate, 'yyyy-MM-dd HH:mm:ss');
            if (is_null($youngerDate)) {
                $youngerDate = new \Zend_Date();
            } else {
                $youngerDate = new \Zend_Date($youngerDate, 'yyyy-MM-dd HH:mm:ss');
            }
            $diff = $youngerDate->sub($olderDate)->toValue();

            return floor($diff / 60);
        } catch (\Zend_Date_Exception $exception) {
            return 1000.00;
        }
    }
}

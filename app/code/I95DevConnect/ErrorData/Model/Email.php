<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model;

use I95DevConnect\ErrorData\Helper\Generic;
use Magento\Framework\App\Area;

/**
 * Model Class to send Email
 */
class Email
{
    public const RECIEVEREMAIL = "reciever_email";
    public const XML_PATH_ENABLED = 'i95devconnect_errors/reports_enabled_settings/report';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    public $inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilderFactory
     */
    public $transportBuilderFactory;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     *
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param Generic $genericHelper
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory $logger,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        Generic $genericHelper
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilderFactory = $transportBuilderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->genericHelper = $genericHelper;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Send Email to the Customer
     *
     * @param string $excelFile
     * @param array $mailDetails
     * @param string $template
     * @param string $subject
     * @param string $message
     *
     * @return boolean
     * @author Ranjith R
     */
    public function sendEmail($excelFile, $mailDetails, $template, $subject, $message)
    {
        try {
            if (!$this->genericHelper->isEnabled()) {
                return false;
            }

            $path = $excelFile === null ? null : explode(DS, $excelFile);
            $filename = $excelFile === null ? null : array_pop($path);

            $ccList = $mailDetails['cc'];
            $ccResultList = array_filter($ccList);
            $this->inlineTranslation->suspend();
            $transport = $this->getTransport(
                $excelFile,
                $filename,
                $mailDetails,
                $template,
                $subject,
                $message,
                $ccResultList
            );
            if ($transport) {
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $transport->reset();
                return true;
            }
        } catch (\Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, 'critical');
        }

        return false;
    }

    /**
     * Get the Transport object to send Email
     *
     * @param string $excelFile
     * @param string $filename
     * @param array $mailDetails
     * @param string $template
     * @param string $subject
     * @param string $message
     * @param array $ccResultList
     *
     * @return boolean|Magento\Framework\Mail\Template\TransportBuilder
     * @noinspection PhpTooManyParametersInspection
     */
    public function getTransport($excelFile, $filename, $mailDetails, $template, $subject, $message, $ccResultList)
    {
        try {
            if (!isset($mailDetails[self::RECIEVEREMAIL]) || empty($mailDetails[self::RECIEVEREMAIL])) {
                return false;
            }

            $fromEmail = isset($mailDetails['from_email']) ? $mailDetails['from_email'] : "";
            $from = isset($mailDetails['from_name']) ? $mailDetails['from_name'] : "";
            $reciever = isset($mailDetails['reciever_name']) ? $mailDetails['reciever_name'] : "";
            $transportBuilder = $this->transportBuilderFactory->create()->setTemplateIdentifier($template)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()])
                ->setTemplateVars(
                    [
                        'customer_name' => $reciever,
                        'store' => $this->storeManager->getStore(),
                        'subject' => $subject,
                        'message' => $message,
                        'greetings_from' => "i95Dev Support"
                    ]
                )
                ->setFrom([
                    'email' => $fromEmail,
                    'name' => $from
                ])
                ->addTo($mailDetails[self::RECIEVEREMAIL], $reciever);

            if ($excelFile) {
                // phpcs:disable
                $transportBuilder->addAttachment($this->fileDriver->fileGetContents($excelFile), $filename, 'csv');
                // phpcs:enable
            }

            foreach ($ccResultList as $cc) {
                $transportBuilder->addCc($cc, "");
            }

            return $transportBuilder->getTransport();
        } catch (\Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, 'critical');
        }

        return false;
    }
}

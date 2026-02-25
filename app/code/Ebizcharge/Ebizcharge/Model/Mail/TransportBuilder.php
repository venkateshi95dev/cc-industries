<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Mail;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as MageTransportBuilder;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Provides access to the admin settings for Ebizcharge
 *
 * Class TransportBuilder
 */
class TransportBuilder extends MageTransportBuilder
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * Class Constructor
     *
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param MessageInterfaceFactory|null $messageFactory
     * @param EmailMessageInterfaceFactory|null $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory|null $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory|null $mimePartInterfaceFactory
     * @param AddressConverter|null $addressConverter
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        MessageInterfaceFactory $messageFactory = null,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory = null,
        MimePartInterfaceFactory $mimePartInterfaceFactory = null,
        AddressConverter $addressConverter = null,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $addressConverter
        );

        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Get Message
     *
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Create Attachment
     *
     * Create attachment items for email based on parameters
     *
     * @param mixed $params
     * @param bool $transport
     * @return $this
     * @throws Exception
     */
    public function createAttachment($params, $transport = false)
    {
        $type = $params['cat'] ?? Mime::TYPE_OCTETSTREAM;
        if ($transport === false) {
            if ($type == 'pdf') {
                $this->message->createAttachment(
                    $params['body'],
                    'application/pdf',
                    Mime::DISPOSITION_ATTACHMENT,
                    Mime::ENCODING_BASE64,
                    $params['name']
                );
            } elseif ($type == 'png') {
                $this->message->createAttachment(
                    $params['body'],
                    'image/png',
                    Mime::DISPOSITION_ATTACHMENT,
                    Mime::ENCODING_BASE64,
                    $params['name']
                );
            } else {
                $encoding = $params['encoding'] ?? Mime::ENCODING_BASE64;
                $this->message->createAttachment(
                    $params['body'],
                    $type,
                    Mime::DISPOSITION_ATTACHMENT,
                    $encoding,
                    $params['name']
                );
            }
        } else {
            $this->addAttachment($params, $transport);
        }
        return $this;
    }

    /**
     * Add Attachment
     *
     * @param mixed $params
     * @param mixed $transport
     * @return void
     * @throws Exception
     */
    public function addAttachment($params, $transport)
    {
        $zendPart = $this->createMimePart($params);
        $parts = $transport->getMessage()->getBody()->addPart($zendPart);

        /** logging the attachment */
        $this->ebizchargeLogger->addInfo(__("Attaching the attchement with mail"));

        $transport->getMessage()->setBody($parts);
    }

    /**
     * Create Mime Part
     *
     * @param mixed $params
     * @return Part
     * @throws Exception
     */
    protected function createMimePart($params)
    {
        if (class_exists(Mime::class) &&
            class_exists(Part::class)) {

            $type = $params['type'] ?? Mime::TYPE_OCTETSTREAM;
            // phpcs:ignore
            $part = new Part(@$params['body']);
            $part->type = $type;
            // phpcs:ignore
            $part->filename = @$params['name'];
            $part->disposition = Mime::DISPOSITION_ATTACHMENT;
            $part->encoding = Mime::ENCODING_BASE64;

            return $part;

        } else {
            $this->ebizchargeLogger->addError(__("Error occured Missing Framework Source"));
            // phpcs:ignore
            throw new Exception("Missing Framework Source");
        }
    }
}

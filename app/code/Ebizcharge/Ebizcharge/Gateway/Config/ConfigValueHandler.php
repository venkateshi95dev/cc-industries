<?php
declare(strict_types=1);

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

namespace Ebizcharge\Ebizcharge\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Ebizcharge config value handler
 *
 * Class ConfigValueHandler
 */
class ConfigValueHandler implements ValueHandlerInterface
{
    /**
     * @const ACH
     */
    public const ACH = 'ACH';

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $configInterface;

    /**
     * @param ConfigInterface $configInterface
     */
    public function __construct(ConfigInterface $configInterface)
    {
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param null|mixed $storeId
     * @return mixed|void
     */
    public function handle(array $subject, $storeId = null)
    {
        $field = SubjectReader::readField($subject);

        if ($field == 'payment_action') {
            $payment = SubjectReader::readPayment($subject);
            $ccType = $payment->getPayment()->getCcType();

            /**
             * ACH bank account only allowed for capture
             */
            if ($ccType == self::ACH) {
                return AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
            }
        }

        return $this->configInterface->getValue(SubjectReader::readField($subject), $storeId);
    }
}

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

namespace Ebizcharge\Ebizcharge\Model\Source;

use Ebizcharge\Ebizcharge\Model\Config;

/**
 * Cc Type Source Model
 *
 * Class CcType
 */
class CcType extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @var Config
     */
    protected Config $ebizConfigModel;

    /**
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param Config $ebizConfigModel
     */
    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig,
        Config                        $ebizConfigModel
    ) {
        parent::__construct($paymentConfig);

        /** @var  configModel */
        $this->ebizConfigModel = $ebizConfigModel;
    }

    /**
     * Get Allowed Types
     *
     * @return array
     */
    public function getAllowedTypes()
    {
        /** @var  $ccTypes */
        $ccTypes = $this->ebizConfigModel->getCardTypes();
        return array_keys($ccTypes);
    }
}

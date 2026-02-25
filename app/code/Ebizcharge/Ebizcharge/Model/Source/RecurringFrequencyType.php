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

use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Recurring Frequency Type Source Model
 *
 * Class RecurringFrequencyType
 */
class RecurringFrequencyType implements OptionSourceInterface
{
    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * RecurringFrequencyType constructor.
     *
     * @param RecurringFactory $recurringFactory
     */
    public function __construct(
        RecurringFactory $recurringFactory
    ) {
        /** @var  _soapApiModel */
        $this->_recurringFactory = $recurringFactory;
    }

    /**
     * To Option Array
     *
     * @return string[][]
     */
    public function toOptionArray(): array
    {
        /** @var  $frequencies */
        $frequencies = $this->_recurringFactory->create()->getRecurringFrequencies();
        $recFrequencies = [];
        if (count($frequencies) > 0) {
            foreach ($frequencies as $frequencyKey => $frequencyTitle) {
                $recFrequencies[] = [
                    'value' => $frequencyKey,
                    'label' => __($frequencyTitle)
                ];
            }
        }
        return $recFrequencies;
    }
}

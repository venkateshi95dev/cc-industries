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

namespace Ebizcharge\Ebizcharge\Override\Component\Control;

use Exception;
use Magento\Framework\View\Element\Html\Date;
use Magento\Ui\Component\Control\Button as CoreButton;

/**
 * Add the calendar type input on admin grid top
 *
 * Class Button
 */
class Button extends CoreButton
{
    /**
     * Custom Template path
     */
    public const CUSTOM_TEMPLATE = 'Ebizcharge_Ebizcharge::uicomponent/control/button/default.phtml';

    /**
     * Get calendar date picker
     *
     * @return false|string
     */
    public function getDateInput(): false|string
    {
        try {
            $datePickerInput = $this->getLayout()->createBlock(Date::class)
                ->setData([
                    'name' => $this->getName(),
                    'id' => $this->getName(),
                    'value' => '',
                    'extra_params' => ' placeholder = " ' . $this->getKey() . '"',
                    'date_format' => 'mm-dd-yy',
                    'years_range' => 'y:c+nn',
                    'max_date' => '+10d',
                    'change_month' => 'false',
                    'change_year' => 'false',
                    'show_on' => 'both',
                    'autocomplete' => 'off',
                    'first_day' => 1
                ])
                ->toHtml();
        } catch (Exception $e) {
            return false;
        }
        return $datePickerInput;
    }

    /**
     * Retrieve name of the calendar
     *
     * @return false|mixed
     */
    public function getName(): mixed
    {
        if ($this->ifEbizOrderGrid()) {
            return $this->getData('type');
        }
        return false;
    }

    /**
     * Check if ebiz custom calendar input
     *
     * @return bool
     */
    public function ifEbizOrderGrid(): bool
    {
        if ($this->getData('type') === 'ebiz-start-date-picker') {
            return $this->getData('type') === 'ebiz-start-date-picker';
        }

        return $this->getData('type') === 'ebiz-end-date-picker';
    }

    /**
     * Get Key
     *
     * @return array|mixed|null
     */
    public function getKey(): mixed
    {
        return $this->getData('key');
    }

    /**
     * Get Label
     *
     * @return array|mixed|null
     */
    public function getLabel(): mixed
    {
        return $this->getData('label');
    }

    /**
     * Get calendar css
     *
     * @return string
     */
    public function getCalendarCss(): string
    {
        // phpcs:ignore
        return sprintf("<style> #%s + button { margin-top: 0.5%%; } #%s { height: 62%%; margin-top:4%%;}</style>", $this->getName(), $this->getName());
    }

    /**
     * @return string
     */
    public function getOnClick()
    {
        $buttonHtml = "";

        if ($this->getData('type') === 'download-orders') {
            return $buttonHtml;
        }
        $buttonHtml =  parent::getOnClick();
        return $buttonHtml;
    }

    /**
     * Retrieve template path
     *
     * @return string
     */
    protected function getTemplatePath()
    {
        return static::CUSTOM_TEMPLATE;
    }
}

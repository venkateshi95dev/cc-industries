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

namespace Ebizcharge\Ebizcharge\Block\System\Config\Form;

use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button as MainButtonClass;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Button class for configurations
 *
 * Class Button
 */
class Button extends Field
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var BackendUrlInterface
     */
    private BackendUrlInterface $backendUrl;

    /**
     * Main Constructor
     *
     * @param BackendUrlInterface $backendUrl
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        BackendUrlInterface $backendUrl,
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        /** @var $config */
        $this->config = $config;
        /** @var $backendUrl */
        $this->backendUrl = $backendUrl;
        /** Set Template */
        $this->setTemplate("Ebizcharge_Ebizcharge::system/config/button.phtml");
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('ebizcharge_ebizcharge/system_config/' . $this->_key);
    }

    /**
     * Get Econnect Yes/No
     *
     * @return bool
     */
    public function getEconnect(): bool
    {
        return $this->config->getEconnect();
    }

    /**
     * Return button function name
     *
     * @return string
     */
    public function getButtonFunction(): string
    {
        return $this->_key;
    }

    /**
     * Generate collect button html
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml(): mixed
    {
        $button = $this->getLayout()->createBlock(MainButtonClass::class)
            ->setData(
                [
                    'id' => $this->_key,
                    'label' => $this->_label,
                    'onclick' => 'javascript:' . $this->_key . '_click(); return false;'
                ]
            );

        return $button->toHtml();
    }

}

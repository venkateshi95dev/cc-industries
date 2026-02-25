<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class StoreButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magedelight_Megamenu::system/config/button/storebutton.phtml';

   /**
    * Remove scope label
    *
    * @param  AbstractElement $element
    * @return string
    */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'menu_active_url' => $this->getUrl('megamenu/cache/cleanmenu')
            ]
        );
        return $this->_toHtml();
    }

    /**
     * Return ajax url for send button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('megamenu/cache/cleanmenu');
    }

    /**
     * Generate send button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'store_button',
                'label' => __('Flush Store Menu'),
            ]
        );

        return $button->toHtml();
    }
}

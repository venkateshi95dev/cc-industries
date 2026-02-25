<?php

namespace Crimson\Testimonial\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Heading extends Template implements RendererInterface
{

    /**
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html  = '';
        $html .= '<div class="system-heading" style="text-align: center;background: #eb5202;padding: 10px;color: #FFF;font-weight: 600;font-size: 1.7rem;margin-bottom: 20px;margin-top: 20px;">';
        $html .= $element->getLabel();
        $html .= '</div>';

        return $html;
    }
}

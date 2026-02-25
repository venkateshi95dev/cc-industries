<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Element;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

/**
 * Class for DateTimePicker
 */
class DateTimePicker extends Field
{
    /**
     * @var  Registry
     */
    public $coreRegistry;

    /**
     * @param Context  $context
     * @param Registry $coreRegistry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)// phpcs:ignore
    {
        //get configuration element
        $html = $element->getElementHtml();

        //check datepicker set or not
        if (!$this->coreRegistry->registry('datepicker_loaded')) {
            $this->coreRegistry->registry('datepicker_loaded', 1);
        }

        //add icon on datepicker
        $html .= '<button type="button" style="display:none;" class="ui-datepicker-trigger '
            . 'v-middle"><span>Select Date</span></button>';

        // add datepicker with element by jquery
        return $html .= '<script type="text/javascript">
            require(["jquery", "jquery/ui", "jquery/jquery-ui-timepicker-addon"], function (jq) {
                jq(document).ready(function () {
                    jq("#' . $element->getHtmlId() . '").datetimepicker({ dateFormat: "dd-mm-yy" });
                    jq(".ui-datepicker-trigger").removeAttr("style");
                    jq(".ui-datepicker-trigger").click(function(){
                        jq("#' . $element->getHtmlId() . '").focus();
                    });
                });
            });
            </script>';
    }
}

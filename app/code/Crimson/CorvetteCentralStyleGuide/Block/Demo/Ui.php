<?php

namespace Crimson\CorvetteCentralStyleGuide\Block\Demo;

use Magento\Framework\View\Element\Template;

class Ui extends Template
{
    protected $_template = 'Crimson_CorvetteCentralStyleGuide::demo/ui.phtml';

    public function cssClassToSelector(string $cssClass, ?string $baseSelector = null): string
    {
        $selectorSuffix = '';

        $cssClassNames = explode(' ', $cssClass);
        foreach ($cssClassNames as $cssClassName) {
            if (!empty($cssClassName)) {
                $selectorSuffix .= '.' . $cssClassName;
            }
        }

        return $baseSelector ? $baseSelector . $selectorSuffix : $selectorSuffix;
    }
}

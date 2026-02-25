<?php

namespace Crimson\Catalog\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class PageSections
 * @package Crimson\Catalog\Model\Category\Attribute\Source
 */
class PageSections extends AbstractSource
{

    CONST NEW_SECTION = "new";
    CONST PURCHASED_SECTION = "purchased";

    /**
     * @var array $_availablePageSections
     */
    protected $_availablePageSections = [
        self::NEW_SECTION       => 'New and Recommended',
        self::PURCHASED_SECTION => 'What Others are Buying',
    ];

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        $sections = $this->_availablePageSections;
        $arrayFinal = [];
        foreach ($sections as $k => $v) {
            $arrayFinal[] = ['value' => $k, 'label' => __($v)];
        }
        array_unshift($arrayFinal, ['value' => '', 'label' => __('Select a section value')]);

        return $arrayFinal;
    }

}

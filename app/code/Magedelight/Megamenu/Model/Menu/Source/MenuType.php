<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Menu\Source;

use Magedelight\Megamenu\Model\Menu;
use Magento\Framework\Data\OptionSourceInterface;

class MenuType implements OptionSourceInterface
{
    /**
     * @var Menu
     */
    protected $megamenuMenu;

    /**
     * MenuType constructor.
     * @param Menu $megamenuMenu
     */
    public function __construct(
        Menu $megamenuMenu
    ) {
        $this->megamenuMenu = $megamenuMenu;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->megamenuMenu->getAvailableTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}

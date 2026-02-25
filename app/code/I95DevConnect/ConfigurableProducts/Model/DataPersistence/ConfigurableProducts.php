<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Model\DataPersistence;

use I95DevConnect\ConfigurableProducts\Model\DataPersistence\ConfigurableProducts\CreateFactory;

/**
 * Configurable product class for sync
 */
class ConfigurableProducts
{
    /**
     *
     * @var CreateFactory
     */
    public $configurableProductCreate;

    /**
     * Constructor for class used for sync of Configurable Products at persistent level
     *
     * @param ConfigurableProducts\CreateFactory $create
     */
    public function __construct(
        CreateFactory $create
    ) {
        $this->configurableProductCreate = $create;
    }

    /**
     * Defining configurable product sync class
     *
     * @param array $stringData
     * @param string $entityCode
     * @return obj
     */
    public function create($stringData, $entityCode)
    {
        return $this->configurableProductCreate->create()->createConfigurableProduct($stringData, $entityCode);
    }
}

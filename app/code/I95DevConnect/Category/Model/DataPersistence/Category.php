<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_Category
 */

namespace I95DevConnect\Category\Model\DataPersistence;

use \I95DevConnect\Category\Model\DataPersistence\Category\Create;

/**
 * Class for Category Creation
 */
class Category
{
    /**
     * @var Category
     */
    public $category;

    /**
     * Category constructor.
     *
     * @param Create $category
     */
    public function __construct(
        Create $category
    ) {
        $this->category = $category;
    }

    /**
     * Creates category
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erpCode
     * @return string
     * @throws \Exception
     */
    public function create($stringData, $entityCode, $erpCode)
    {
        return $this->category->create($stringData, $entityCode, $erpCode);
    }
}

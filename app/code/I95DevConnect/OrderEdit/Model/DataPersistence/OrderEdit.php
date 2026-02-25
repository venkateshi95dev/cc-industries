<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Model\DataPersistence;

use I95DevConnect\OrderEdit\Model\DataPersistence\OrderEdit\EditOrder;

/**
 * Class for edit order information
 */
class OrderEdit
{

    /**
     * @var EditOrder
     */
    public $orderEdit;

    /**
     *
     * @param EditOrder $orderEdit
     */
    public function __construct(
        EditOrder $orderEdit
    ) {
        $this->orderEdit = $orderEdit;
    }

    /**
     * Edit order by ERP data string
     *
     * @param array $stringData
     * @param string $entityCode
     * @param string|null $erp
     * @return string
     */

    public function create($stringData, $entityCode, $erp) // NOSONAR
    {
        return $this->orderEdit->edit($stringData);
    }
}

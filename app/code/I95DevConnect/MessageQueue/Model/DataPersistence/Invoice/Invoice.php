<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Invoice;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Model\DataPersistence\Invoice\Invoice\CreateFactory;

/**
 * Class for creating invoice, geting invoice info and seting invoice response
 */
class Invoice
{
    /**
     *
     * @var CreateFactory
     */
    public $createFactory;

    /**
     *
     * @param CreateFactory $createFactory
     */
    public function __construct(
        CreateFactory $createFactory
    ) {
        $this->createFactory = $createFactory;
    }

    /**
     * Create Invoice.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     *
     * @return I95DevResponseInterface
     * @updatedBy Arushi Bansal
     */
    public function create($stringData, $entityCode, $erp)
    {
        return $this->createFactory->create()->createInvoice($stringData, $entityCode, $erp);
    }
}

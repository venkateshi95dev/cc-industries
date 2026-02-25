<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Setup;

use I95DevConnect\MessageQueue\Model\EntityList;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Setup\Module\DataSetup;

/**
 * Class for handling data removal during module uninstall
 *
 * @api
 */
class UninstallData implements UninstallInterface
{
    /**
     * @var CustomerSetupFactory
     */
    public $customerSetupFactory;

    /**
     * @var DataSetup
     */
    public $dataSetup;
    /**
     * @var EntityList
     */
    public $entityList;

    /**
     * UninstallData constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param DataSetup $dataSetup
     * @param EntityList $entityList
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        DataSetup $dataSetup,
        EntityList $entityList
    ) {

        $this->customerSetupFactory = $customerSetupFactory;
        $this->dataSetup = $dataSetup;
        $this->entityList = $entityList;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $setup->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->dataSetup]);
        $customerSetup->removeAttribute(
            Customer::ENTITY,
            'pricelevel'
        );

        $setup->endSetup();
    }
}

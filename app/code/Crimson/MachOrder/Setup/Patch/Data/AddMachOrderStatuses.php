<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/27/2019 10:58 AM
 * @brief
 */

namespace Crimson\MachOrder\Setup\Patch\Data;

use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddMachOrderStatuses implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * AddPaypalOrderStates constructor.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param SalesSetupFactory                                 $salesSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * Prepare database for install
         */
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Install order statuses from config
         */
        $data = [];
        $statuses = [
            'infulfillment' => __('In Fulfillment'),
            'shipped' => __('Shipped'),
        ];
        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            $data
        );

        $stateTable = $this->moduleDataSetup->getTable('sales_order_status_state');
        $connection = $salesSetup->getConnection();

        $connection->delete($stateTable, $connection->quoteInto('status IN (?)', [
            'closed',
            'pending',
            'processing',
            'infulfillment',
            'shipped',
        ]));

        //reset rules to match Zip M1
        $data = [
            [
                'status' => 'closed',
                'state' => 'closed',
                'is_default' => '0',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'infulfillment',
                'state' => 'pending_payment',
                'is_default' => '1',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'infulfillment',
                'state' => 'processing',
                'is_default' => '1',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'pending',
                'state' => 'new',
                'is_default' => '0',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'pending',
                'state' => 'payment_review',
                'is_default' => '1',
                'visible_on_front' => '0',
            ],
            [
                'status' => 'pending',
                'state' => 'pending_payment',
                'is_default' => '0',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'pending',
                'state' => 'processing',
                'is_default' => '0',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'processing',
                'state' => 'processing',
                'is_default' => '0',
                'visible_on_front' => '1',
            ],
            [
                'status' => 'shipped',
                'state' => 'closed',
                'is_default' => '1',
                'visible_on_front' => '1',
            ],
        ];

        $salesSetup->getConnection()
            ->insertMultiple(
                $this->moduleDataSetup->getTable('sales_order_status_state'),
                $data
            );

        /**
         * Prepare database after install
         */
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.3.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

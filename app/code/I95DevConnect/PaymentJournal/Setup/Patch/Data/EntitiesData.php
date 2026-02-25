<?php

/**
 * @author    Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_PaymentJournal
 */

namespace I95DevConnect\PaymentJournal\Setup\Patch\Data;

use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class EntitiesData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var Magento\Framework\App\State $appState
     */
    private $appState;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Entities data constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $appState,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /* Set the area code and catch exception if thrown */
        try {
            $this->appState->setAreaCode('global');
        } catch (LocalizedException $exception) {
            $this->logger->createLog(
                __METHOD__,
                $exception->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('i95dev_entity'),
            [
                [
                    'entity_name' => 'Payment Journal',
                    'entity_code' => 'paymentJournal',
                    'sort_order' => 16,
                    'support_for_inbound' => false,
                    'support_for_outbound' => true
                ]
            ]
        );
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() //NOSONAR
    {
        return [];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.2';
    }
}

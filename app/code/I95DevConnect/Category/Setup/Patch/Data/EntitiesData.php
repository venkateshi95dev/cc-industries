<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_Category
 */

namespace I95DevConnect\Category\Setup\Patch\Data;

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
                    'entity_name' => 'Category',
                    'entity_code' => 'category',
                    'sort_order' => 1,
                    'support_for_inbound' => true,
                    'support_for_outbound' => false
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
        return '2.0.0';
    }
}

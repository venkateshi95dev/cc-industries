<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        4/5/2019 10:55 AM
 * @brief
 */
namespace Crimson\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class UpdatePayPalConfiguration implements
    DataPatchInterface,
    PatchVersionInterface
{
    /**
     *  @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    public function __construct(
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface
    )
    {
        $this->_configWriter = $configInterface;
    }

    public function apply()
    {
        $this->_configWriter->saveConfig('amfile/block/block_location',  'no', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
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
        return '1.0.1';
    }
}
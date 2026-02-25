<?php

namespace Crimson\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateConfigData20221104 implements DataPatchInterface
{
    /** @var WriterInterface */
    private $configWriter;

    public function __construct(
        WriterInterface $configWriter
    ) {
        $this->configWriter = $configWriter;
    }

    public function apply() 
    { 
        $this->configWriter->save('design/header/logo_width', 135);
        $this->configWriter->save('design/header/logo_height', 95);
    }

    public function getAliases() 
    { 
        return [];
    }

    public static function getDependencies() 
    { 
        return [];
    }
}
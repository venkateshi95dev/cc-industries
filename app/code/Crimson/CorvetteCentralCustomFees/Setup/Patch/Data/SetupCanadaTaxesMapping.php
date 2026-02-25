<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentralCustomFees\Setup\Patch\Data;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class SetupCanadaTaxesMapping implements DataPatchInterface
{
    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function apply(): void
    {
        $mappingJson = <<<JSON
{
    "_1602796687199_185":{"province":"ON","jurisdiction_code":"HST","value":"13","apply_to":"retail"},
    "_1602796687199_186":{"province":"ON","jurisdiction_code":"GST","value":"5","apply_to":"dealer"},
    "_1602796687199_187":{"province":"PE","jurisdiction_code":"HST","value":"15","apply_to":"retail"},
    "_1602796687199_188":{"province":"PE","jurisdiction_code":"GST","value":"5","apply_to":"dealer"},
    "_1602796687199_189":{"province":"BC","jurisdiction_code":"PST","value":"7","apply_to":"retail"},
    "_1602796687199_190":{"province":"BC","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_191":{"province":"MB","jurisdiction_code":"PST","value":"7","apply_to":"retail"},
    "_1602796687199_192":{"province":"MB","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_193":{"province":"NB","jurisdiction_code":"GST","value":"15","apply_to":"retail"},
    "_1602796687199_194":{"province":"NL","jurisdiction_code":"HST","value":"15","apply_to":"retail"},
    "_1602796687199_195":{"province":"NL","jurisdiction_code":"GST","value":"5","apply_to":"dealer"},
    "_1602796687199_196":{"province":"NS","jurisdiction_code":"HST","value":"15","apply_to":"retail"},
    "_1602796687199_197":{"province":"NS","jurisdiction_code":"GST","value":"5","apply_to":"dealer"},
    "_1602796687199_198":{"province":"QC","jurisdiction_code":"PST","value":"9.975","apply_to":"retail"},
    "_1602796687199_199":{"province":"QC","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_200":{"province":"SK","jurisdiction_code":"PST","value":"6","apply_to":"retail"},
    "_1602796687199_201":{"province":"SK","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_202":{"province":"NL","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_203":{"province":"NU","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_204":{"province":"NT","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_205":{"province":"SK","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_206":{"province":"SK","jurisdiction_code":"PST","value":"6","apply_to":"retail"},
    "_1602796687199_207":{"province":"YT","jurisdiction_code":"GST","value":"5","apply_to":"all"},
    "_1602796687199_208":{"province":"AB","jurisdiction_code":"GST","value":"5","apply_to":"all"}
}
JSON;

        // this mapping can be set globally since it won't be enabled in other store
        $this->configWriter->save(
            'custom_fees/canada_taxes/mapping',
            $mappingJson,
            'default',
            0
        );
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}

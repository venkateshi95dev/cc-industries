<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Crimson\CokerWV\Api\WVStoreInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\File\CsvFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

class AddingCokerWVShippingMethodsConfig implements DataPatchInterface
{
    const CSV_FILE_PATH = '/import/coker_zip_data_migration/shipping_methods.csv';

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager,
        private readonly \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
    ) {}

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $configData = [
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/active',
                'value'    => '0',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/title',
                'value'    => 'Lof Per Product Shipping',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/name',
                'value'    => 'Per Product Shipping',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/defaultprice',
                'value'    => '10',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/shippingbasedon',
                'value'    => '0',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/sallowspecific',
                'value'    => '0',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/lofproductshipping/specificcountry',
                'value'    => null,
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/account',
                'value'    => $this->encryptorInterface->encrypt('118388801'),
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/active',
                'value'    => '1',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/active_rma',
                'value'    => '0',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/allowed_methods',
                'value'    => 'FEDEX_2_DAY,FEDEX_GROUND,GROUND_HOME_DELIVERY,INTERNATIONAL_ECONOMY,INTERNATIONAL_GROUND',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/debug',
                'value'    => '1',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/free_method',
                'value'    => 'FEDEX_GROUND',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/free_shipping_enable',
                'value'    => '1',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/free_shipping_subtotal',
                'value'    => '999999',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/handling_action',
                'value'    => 'P',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/handling_fee',
                'value'    => '40',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/handling_type',
                'value'    => 'P',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/key',
                'value'    => $this->encryptorInterface->encrypt('NatagI50HH2rahDq'),
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/max_package_weight',
                'value'    => '120',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/meter_number',
                'value'    => $this->encryptorInterface->encrypt('103493698'),
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/password',
                'value'    => $this->encryptorInterface->encrypt('rRHwRud6LT4BfcZnhj0BladjT'),
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/residence_delivery',
                'value'    => '1',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/sallowspecific',
                'value'    => '1',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/shipment_requesttype',
                'value'    => '0',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/showmethod',
                'value'    => '0',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/smartpost_hubid',
                'value'    => null,
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/sort_order',
                'value'    => '1',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/specificcountry',
                'value'    => 'AF,AX,AL,DZ,AS,AD,AO,AI,AQ,AG,AR,AM,AW,AU,AT,AZ,BS,BH,BD,BB,BY,BE,BZ,BJ,BM,BT,BO,BA,BW,BV,IO,VG,BN,BG,BF,BI,KH,CM,CA,CV,BQ,KY,CF,TD,CL,CN,CX,CC,CO,KM,CG,CD,CK,CR,CI,HR,CU,CW,CY,CZ,DK,DJ,DM,DO,EC,EG,SV,GQ,ER,EE,SZ,ET,FK,FO,FJ,FI,FR,GF,PF,TF,GA,GM,GE,DE,GH,GI,GR,GL,GD,GP,GU,GT,GG,GN,GW,GY,HT,HM,HN,HK,HU,IS,ID,IR,IQ,IE,IM,IL,IT,JM,JP,JE,JO,KZ,KE,KI,XK,KW,KG,LA,LV,LB,LS,LR,LY,LI,LT,LU,MO,MG,MW,MY,MV,ML,MT,MH,MQ,MR,MU,YT,FM,MD,MC,MN,ME,MS,MA,MZ,MM,NA,NR,NP,NL,NC,NZ,NI,NE,NG,NU,NF,MP,MK,NO,OM,PK,PW,PS,PA,PG,PY,PE,PH,PN,PL,PT,QA,RE,RO,RW,WS,SM,ST,SA,SN,RS,SC,SL,SG,SX,SK,SI,SB,SO,ZA,GS,KR,ES,LK,BL,SH,KN,LC,MF,PM,VC,SD,SR,SJ,SE,CH,SY,TW,TJ,TZ,TH,TL,TG,TK,TO,TT,TN,TR,TM,TC,TV,UG,UA,AE,GB,US,UY,UM,VI,UZ,VU,VA,VE,VN,WF,EH,YE,ZM,ZW',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/specificerrmsg',
                'value'    => 'There was an error processing your order online.  Please contact us by phone to place your order: 1-800-251-6336',
            ],
            [
                'scope_id' => '0',
                'path'     => 'carriers/fedex/title',
                'value'    => 'FedEx',
            ],
        ];
        $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
        $WVWebsiteId = $this->storeManager->getWebsite(WVStoreInterface::WV_WEBSITE_CODE)->getId();
        foreach ($configData as $data) {
            $scopeId = 0;
            if ($data['scope_id'] == '2')
                $scopeId = $cokerWebsiteId;
            elseif ($data['scope_id'] == '3')
                $scopeId = $WVWebsiteId;

            $path  = $data['path'];
            $value = $data['value'];
            if ($scopeId > 0) {
                $this->configWriter->save($path, $value, 'websites', $scopeId);
            } else {
                // If scopeId = 0, write separated config for each website instead of overwrite default config
                $this->configWriter->save($path, $value, 'websites', $cokerWebsiteId);
                $this->configWriter->save($path, $value, 'websites', $WVWebsiteId);
            }
        }
    }


    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
        ];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SetShareCustomerAccountsToWebsite implements DataPatchInterface
{

    CONST CUSTOMER_SHARE_ACCOUNTS = "customer/account_share/scope";

    public function __construct(
        private readonly WriterInterface $configWriter
    ) {}

    public function apply(): void
    {
        $this->configWriter->save(self::CUSTOMER_SHARE_ACCOUNTS, 1);
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class
        ];
    }
}

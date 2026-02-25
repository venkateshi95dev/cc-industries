<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Integration\Api\IntegrationServiceInterface;

class AddIntegrationSettings implements DataPatchInterface
{

    CONST INTEGRATION_NAME = 'GoDataFeed';

    public function __construct(
        private readonly IntegrationServiceInterface $IntegrationServiceInterface,
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {}

    public function apply(): void
    {
        $integrationTmp = $this->IntegrationServiceInterface->findByName(self::INTEGRATION_NAME);
        if ($integrationTmp && $integrationTmp->getId()) {
            return;
        }

        //Creating Integration and updating consumer and token
        $integrationData = [
            'name' => self::INTEGRATION_NAME,
            'email' => 'cokergroupwebteam@gmail.com',
            'status' => 1,
        ];
        $integration = $this->IntegrationServiceInterface->create($integrationData);
        $consumerId    = $integration->getConsumerId();
        $connection = $this->moduleDataSetup->getConnection();
        $connection
            ->update(
                $connection->getTableName('oauth_consumer'),
                ['key' => 'dc9yv1dec44cc6329ujqqt548nthn1ps', 'secret' => '1:3:KzNFpL+3Og8NlvWnZlXWIj2R2qNeau/FKvmOn1gVi/U7EQkZuKVjIJ6w6MzXzlLf6kZkM+1aH1onytfC'],
                ['entity_id = ?' => $consumerId]
            );

        $data[] = [
            'token'        => 'ptz15m483w8e34ly5hzqbssc90etwlok',
            'secret'       => '1:3:VMB+hKSx+j2ApB5ItiMC6HOoFn25tmpCxBIPE9WtB/xOG/+MnPqrkc07GYDHnUbSYoxSk50BNISHN8ZR',
            'verifier'     => '6ri3xt45o10p9c73i5wnz0aqg712pep5',
            'consumer_id'  => $consumerId,
            'type'         => "access",
            'callback_url' => "oob",
            'user_type'    => 1,
        ];
        $connection->insertArray(
            $connection->getTableName('oauth_token'),
            ['token', 'secret', 'verifier', 'consumer_id', 'type', 'callback_url', 'user_type'],
            $data
        );
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [
            CreateCokerWebiste::class,
            CreateWVWebiste::class,
            SetShareCustomerAccountsToWebsite::class
        ];
    }
}

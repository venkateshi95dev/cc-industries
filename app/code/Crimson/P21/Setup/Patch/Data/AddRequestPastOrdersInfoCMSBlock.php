<?php
declare(strict_types=1);

namespace Crimson\P21\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Psr\Log\LoggerInterface;
use Exception;

class AddRequestPastOrdersInfoCMSBlock implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private BlockFactory $blockFactory,
        private BlockRepositoryInterface $blockRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            $content = '<p class="request-past-orders-info">'
                . 'In case you need to see your past orders, please reach over to our support team.'
                . '</p>';

            $cmsBlockData = [
                'title' => 'Request Past Orders Info Message',
                'identifier' => 'request_past_orders_info_message',
                'content' => $content,
                'is_active' => 1,
                'stores' => [0]
            ];

            $block = $this->blockFactory->create()->setData($cmsBlockData);
            $this->blockRepository->save($block);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

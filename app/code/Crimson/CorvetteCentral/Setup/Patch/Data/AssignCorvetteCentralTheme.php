<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Theme\Model\ThemeFactory;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;

class AssignCorvetteCentralTheme implements DataPatchInterface
{

    const THEME_CONFIG_PATH            = 'design/theme/theme_id';
    const CORVETTE_CENTRAL_THEME_CODE  = 'CorvetteCentral/default';

    protected array $_themes = [];

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly StoreManagerInterface $storeManager,
        private readonly Theme $themeResourceModel,
        private readonly ThemeFactory $themeFactory,
        private readonly Config $themeConfig,
        private readonly StoreRepositoryInterface $storeRepositoryInterface,
    ) {
        $this->_themes = $this->_getAllThemes();
    }

    public function apply(): void
    {
        $ccStoreId = $this->storeRepositoryInterface->get(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        $ccWebsiteId = $this->storeManager->getWebsite(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE)->getId();
        $ccThemeId = $this->_getThemeIdOnZIP(self::CORVETTE_CENTRAL_THEME_CODE);
        if ($ccThemeId) {
            $ccTheme = $this->themeFactory->create()->load($ccThemeId);
            $this->themeConfig->assignToStore($ccTheme, [$ccStoreId]);
            $this->configWriter->save(self::THEME_CONFIG_PATH, $ccThemeId, ScopeInterface::SCOPE_WEBSITES, $ccWebsiteId);
        }
    }

    /**
     * @param $themeCode
     * @return int|null
     */
    private function _getThemeIdOnZIP($themeCode): ?int
    {
        $found = array_filter($this->_themes,function($v,$k) use ($themeCode) {
            return $v['code'] == $themeCode;
        },ARRAY_FILTER_USE_BOTH);
        $found = array_first($found);

        return !empty($found['theme_id']) ? (int) $found['theme_id'] : null;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function _getAllThemes(): array
    {
        $select = $this->themeResourceModel->getConnection()->select()
            ->from(['main_table' => $this->themeResourceModel->getMainTable()]);
        $data = $this->themeResourceModel->getConnection()->fetchAll($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateCorvetteCentralWebiste::class
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

<?php

namespace Crimson\CokerWV\Service;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Crimson\CokerWV\Api\WVStoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Theme\Model\ThemeFactory;

class SetThemesConfig
{

    const THEME_CONFIG_PATH = 'design/theme/theme_id';
    const COKER_THEME_CODE  = 'Silk/coker';
    const WV_THEME_CODE     = 'Silk/wheel_vintiques';
    const BLANK_THEME_CODE  = 'Silk/blank';

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

    /**
     * @return array
     */
    public function execute(): array
    {
        $result['message'] = "All Themes set.";

        try {
            $cokerWebsiteId = $this->storeManager->getWebsite(CokerStoreInterface::COKER_WEBSITE_CODE)->getId();
            $cokerDefaultStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_DEFAULT_STORE_CODE)->getId();
            $cokerStoreId = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE)->getId();
            $cokerThemeId = $this->_getThemeIdOnZIP(self::COKER_THEME_CODE);
            if ($cokerThemeId) {
                $cokerTheme = $this->themeFactory->create()->load($cokerThemeId);
                $this->themeConfig->assignToStore($cokerTheme, [$cokerDefaultStoreId, $cokerStoreId]);
                $this->configWriter->save(self::THEME_CONFIG_PATH, $cokerThemeId, ScopeInterface::SCOPE_WEBSITES, $cokerWebsiteId);
            }

            $wvWebsiteId = $this->storeManager->getWebsite(WVStoreInterface::WV_WEBSITE_CODE)->getId();
            $wvStoreId   = $this->storeRepositoryInterface->get(WVStoreInterface::WV_STORE_CODE)->getId();
            $wvThemeId    = $this->_getThemeIdOnZIP(self::WV_THEME_CODE);
            $blankThemeId = $this->_getThemeIdOnZIP(self::BLANK_THEME_CODE);
            if ($blankThemeId) {
                $this->configWriter->save(self::THEME_CONFIG_PATH, $blankThemeId, ScopeInterface::SCOPE_WEBSITES, $wvWebsiteId);
            }
            if ($wvThemeId) {
                $wvTheme = $this->themeFactory->create()->load($wvThemeId);
                $this->themeConfig->assignToStore($wvTheme, [$wvStoreId]);
            }

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            return $result;
        }

        return $result;
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
}

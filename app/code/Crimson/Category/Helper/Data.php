<?php
/**
 * @namespace   Crimson
 * @module      Category
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        12/26/2018
 */
namespace Crimson\Category\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Crimson\Category\Helper
 */
class Data extends AbstractHelper
{

    const XPATH_MOSTVIEWED_ENABLED = 'catalog/crimson_most_viewed/enabled';
    const XPATH_MOSTVIEWED_AGE     = 'catalog/crimson_most_viewed/event_age';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var StoreManagerInterface $_storeManager */
    protected $_storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param $color
     */
    public function setConsoleColor($color)
    {

        switch ($color) {

            case 'yellow':
                echo "\033[0;33m";
                break;

            case 'green':
                echo "\033[32m";
                break;

            case 'red':
                echo "\033[0;31m";
                break;

            case 'white':
                echo "\033[0m";
                break;
        }
    }

    /**
     * show a status bar in the console
     *
     * $this->_helper->setConsoleColor('green');
     * $currentCategoryCount = 1;
     * $this->_helper->showStatus(0, count((array)$allCategories));
     * foreach ($allCategories as $categoryId => $categoryInformation) {
     *    $this->_helper->showStatus($currentCategoryCount, count((array)$allCategories));
     *    $currentCategoryCount++;
     * }
     * $this->_helper->setConsoleColor('white');
     *
     *
     * <code>
     * for($x=1;$x<=100;$x++){
     *
     *     show_status($x, 100);
     *
     *     usleep(100000);
     *
     * }
     * </code>
     *
     * @param   int     $done   how many items are completed
     * @param   int     $total  how many items are to be done total
     * @param   int     $size   optional size of the status bar
     * @return  void
     *
     */
    public function showStatus($done, $total, $size = 30)
    {
        static $start_time;

        // if we go over our bound, just ignore it
        if($done > $total) return;

        if(empty($start_time) || $done == 0) $start_time=time();
        $now = time();

        $perc = (double) ($done/$total);

        $bar = floor($perc * $size);

        $this->setConsoleColor('white');

        $statusBar="\r[";
        $statusBar .= str_repeat("#", $bar);

        if ($bar < $size) {
            $statusBar .= "";
            $statusBar .= str_repeat(" ", $size-$bar);
        } else {
            $statusBar .= "#";
        }

        $disp = number_format($perc * 100, 0);

        $statusBar .= "]";

        $this->setConsoleColor('green');

        $statusBar .= " $disp% - $done processed of $total in total";

        echo "$statusBar";

        flush();

        // when done, send a newline
        if($done === $total) {
            echo "\n";
        }

    }

    /**
     * @return bool
     */
    public function isMostViewedSectionEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_MOSTVIEWED_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return string|null
     */
    public function getMostViewedEventAge(): ?string
    {
        $age = $this->scopeConfig->getValue(self::XPATH_MOSTVIEWED_AGE, ScopeInterface::SCOPE_WEBSITE);
        return $age ? (string) $age : null;
    }
}

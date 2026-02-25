<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Magento\Customer\Block\Account\SortLink;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Ebizcharge\Ebizcharge\Model\Config;

/**
 * Accesses data to pass to the
 * Manage My Payment Method pages
 *
 * Class Navigation
 */
class Navigation extends SortLink
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * Main Constructor of the Class
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Config $config
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Config $config
    ) {
        /** construct Method */
        parent::__construct($context, $defaultPath);

        /** @var  _defaultPath */
        $this->_defaultPath = $defaultPath;
        /** @var config */
        $this->config = $config;
    }

    /**
     * To HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->config->isEbizchargeActive() == 1 && $this->config->isRecurringActive() == 1) {
            return parent::_toHtml();
        }
        return '';
    }
}

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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\CustomerEdit\Tab;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface as TabInterface;

/**
 * Customer payment methods
 *
 * Class PaymentMethods
 */
class PaymentMethods extends Template implements TabInterface
{
    /**
     * URL Path for cards listing
     *
     * @const CARDS_TAB_PATH
     */
    public const CARDS_TAB_PATH = 'ebizcharge_ebizcharge/cards/index';

    /**
     * URL Path for bank accounts listing
     *
     * @const BANK_ACCOUNTS_TAB_PATH
     */
    public const BANK_ACCOUNTS_TAB_PATH = 'ebizcharge_ebizcharge/ach/index';

    /**
     * @var Registry
     */
    protected Registry $_coreRegistry;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * PaymentMethods constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param EbizchargeLogger $ebizchargeLogger
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EbizchargeLogger $ebizchargeLogger,
        array $data = []
    ) {
        /** @var  _coreRegistry */
        $this->_coreRegistry = $registry;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;

        parent::__construct($context, $data);
    }

    /**
     * Get Tablabel
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __($this->getData('tab_label'));
    }

    /**
     * Get Tab Title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __($this->getData('tab_label'));
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl($this->getTabPath(), ['_current' => true]);
    }

    /**
     * Is Ajax Loaded
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }

    /**
     * Can Show Tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool)$this->getCustomerId();
    }

    /**
     * Get Customer Id
     *
     * @return mixed|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Id Hidden
     *
     * @return false
     */
    public function isHidden()
    {
        return false;
    }
}

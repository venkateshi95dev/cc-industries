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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Recurring;

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring Customer Name Ui Column Component
 *
 * Class CustomerName
 */
class CustomerName extends Column
{
    /**
     * @var Escaper
     */
    protected Escaper $_escaper;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * CustomerName constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $urlInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CustomerFactory $customerFactory,
        UrlInterface $urlInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  escaper */
        $this->_escaper = $escaper;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $columnName = nl2br($this->_escaper->escapeHtml($item[$this->getData('name')]));
                $customerEmail = $item['customer_email'];
                $item[$this->getData('name')] = $this->getLabelInfo($columnName, $customerEmail);
            }
        }

        return $dataSource;
    }

    /**
     * Get html as per getLabelInfo
     *
     * @param mixed $gridLabel
     * @param null|mixed $customerEmail
     * @return string
     */
    private function getLabelInfo($gridLabel, $customerEmail = null): string
    {
        $gridLabel = $gridLabel ? $gridLabel : __('*');
        $cssClass = 'customer-male-icon';
        try {
            /** @var  $customer */
            $customer = $this->_customerFactory->create()->loadByEmail($customerEmail);
            $customerId = $customer->getEntityId();
            $url = $this->_urlInterface->getUrl('customer/index/edit', ['id' => $customerId]);

            return '<a href="' . $url . '" target="_parent" title="' .
                __("Please click to go to customer.") . '" > <span class="' . $cssClass .
                '"><span>' . $gridLabel . '</span></span></a>';
        } catch (LocalizedException $localizedException) {
            return '<span class="' . $cssClass . '"><span>' . $gridLabel . '</span></span>';
        }
    }
}

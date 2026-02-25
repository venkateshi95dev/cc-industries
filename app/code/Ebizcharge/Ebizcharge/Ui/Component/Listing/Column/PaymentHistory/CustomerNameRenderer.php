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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\PaymentHistory;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class CustomerNameRenderer
 */
class CustomerNameRenderer extends Column
{
    /**
     * @var Config
     */
    protected Config $_configModel;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlBuilder;

    /**
     * CustomerNameRenderer constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Config $configModel
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $configModel,
        CustomerFactory $customerFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _urlBuilder */
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $customerName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$customerName])) {
                    $item[$customerName] = $this->getCardInfoHtml($item[$customerName], $item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get status html as per $resultCardInfo
     *
     * @param mixed $customerName
     * @param mixed $item
     * @return string
     * @throws LocalizedException
     */
    private function getCardInfoHtml($customerName, $item): string
    {
        $customerEmail = isset($item['email'])?$item['email']:'';
        /** @var  $customer */
        $customer = $this->_customerFactory->create()->loadByEmail($customerEmail);
        $cssClass = 'customer-male-icon';

        if ($customer->getId()) {
            $customerId = $customer->getId();
            $customerLink = $this->_urlBuilder->getUrl(
                'customer/index/edit/id',
                [
                    'id' => $customerId
                ]
            );

            return '<a href="' . $customerLink . '" target="_blank"> <span class="' . $cssClass .
                '"><span>' . $customerName . '</span></span></a>';
        } else {
            return '<span class="' . $cssClass . '"><span>' . $customerName . '</span></span>';
        }
    }
}

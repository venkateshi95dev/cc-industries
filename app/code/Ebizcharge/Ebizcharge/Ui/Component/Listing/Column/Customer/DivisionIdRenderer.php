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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Customer;

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Division Id Renderer Ui Component
 *
 * Class DivisionIdRenderer
 */
class DivisionIdRenderer extends Column
{
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * DivisionIdRenderer constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerFactory $customerFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerFactory $customerFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
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
                $customerId = $item['entity_id'];
                $customer = $this->_customerFactory->create()->load($customerId);
                $item[$this->getData('name')] = $customer->getEcDivisionId();
            }
        }
        return $dataSource;
    }
}

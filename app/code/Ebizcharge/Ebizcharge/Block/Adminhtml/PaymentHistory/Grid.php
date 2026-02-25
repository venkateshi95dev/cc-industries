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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Customer;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Ebizcharge\Ebizcharge\Model\PaymentHistory\Collection as PaymentHistoryCollection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Payment History Grid block class
 *
 * Class Grid
 */
class Grid extends Extended
{
    /**
     * @var PaymentHistoryCollection
     */
    protected PaymentHistoryCollection $_paymentHistorycollection;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var Customer
     */
    protected Customer $_customerModel;

    /**
     * Grid constructor.
     *
     * @param PaymentHistoryCollection $collection
     * @param Context $context
     * @param Data $backendHelper
     * @param Customer $customerModel
     * @param EbizchargeLogger $ebizchargeLogger
     * @param array $data
     */
    public function __construct(
        PaymentHistoryCollection $collection,
        Context $context,
        Data $backendHelper,
        Customer $customerModel,
        EbizchargeLogger $ebizchargeLogger,
        array $data = []
    ) {
        /** Parent Reconstruct */
        parent::__construct($context, $backendHelper, $data);

        /** @var  collection */
        $this->_paymentHistorycollection = $collection;
        /** @var ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerModel */
        $this->_customerModel = $customerModel;
    }

    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = '';
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        // phpcs:ignore
        $searchKeywords = $_REQUEST['search_keywords'] ?? '';

        if ($this->getFilterVisibility()) {
            $html .= '<div class="data-grid-search-control-wrap search-textbox-panel" >
                        <form action="' . $currentUrl . '" name="keywords" id="keywords">
                        <label class="data-grid-search-label"  title="Search" for="fulltext">
                            <span >Search</span>
                        </label>
                        <input class="admin__control-text data-grid-search-control" type="text"
                         value="'.$searchKeywords.'" name="search_keywords" id="search_keywords"
                          placeholder="Search by keyword">
                        <button class="action-submit" type="submit"><span>Search</span></button>
                        </form>';
            $html.='</div>';
            $html .= $this->getResetFilterButtonHtml();

        }
        return $html;
    }

    /**
     * Reset Filter Button Html
     *
     * @return string
     */
    public function getResetFilterButtonHtml()
    {
        $html = '<div class="search-textbox-panel reset-button">
        <a href="'.$this->getUrl('ebizcharge_ebizcharge/recurrings/history').'" target="_parent"
         title="Reset Filter">Reset Filters</a>
        </div>
        ';
        return $html;
    }

    /**
     * Initialize child blocks
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(Button::class)->setData(
                [
                    'label' => __('Search Payment History'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'task action-secondary',
                ]
            )->setDataAttribute(
                [
                    'action' => 'grid-filter-apply'
                ]
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Prepare Collection for Grid
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareCollection(): Grid
    {
        if ($this->applyFilters()) {
            $this->setCollection($this->_paymentHistorycollection);
            return parent::_prepareCollection();
        }
        /** @var  $recurringPayments */
        $customerId=null;
        /** @var  $start */
        $start = $this->getPageNumber() * $this->getPageSize();
        /** @var  $limit */
        $limit = $this->getPageSize();
        //$paymentDate = null;

        /** @var $recurringPaymentsCollection */
        $recurringPaymentsCollection = $this->_paymentHistorycollection->getCollection(
            $customerId,
            $start,
            $limit,
        );

        /** set collection */
        $this->setCollection($recurringPaymentsCollection);

        //if ($this->getCollection()) {
        //    foreach ($recurringPaymentsCollection as $field => $value) {
              // $this->getCollection()->addFieldToFilter($field, $value);
        //    }
        //}

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Apply Filters
     *
     * @return bool
     */
    public function applyFilters(): bool
    {
        return !empty(parent::getParam(parent::getVarNameFilter())) &&
            is_string(parent::getParam(parent::getVarNameFilter()));
    }

    /**
     * Get Page Number
     *
     * @return int
     */
    public function getPageNumber(): int
    {
        return (int)$this->getParam($this->getVarNamePage(), $this->_defaultPage);
    }

    /**
     * Get Page Size
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return (int)$this->getParam($this->getVarNameLimit(), $this->_defaultLimit);
    }

    /**
     * Prepare Columns
     *
     * @return $this
     */
    protected function _prepareColumns(): Grid
    {
        try {
            $this->addColumn(
                'massActionField',
                [
                    'header' => __('refNum'),
                    'type' => 'text',
                    'sortable' => true,
                    'is_system' => true,
                    'filter' => false,
                    'index' => 'refNum',
                    'column_css_class' => 'no-display',
                    'header_css_class' => 'no-display'
                ]
            )
            ->addColumn(
                'refNum',
                [
                    'header' => __('Ref #'),
                    'type' => 'text',
                    'index' => 'refNum',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'customerId',
                [
                    'header' => __('Customer ID'),
                    'type' => 'id',
                    'sortable' => false,
                    'filter' => false,
                    'index' => 'customerId',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id'
                ]
            )
            ->addColumn(
                'customerName',
                [
                    'header' => __('Customer Name'),
                    'index' => 'customerName',
                    'filter' => false,
                    'sortable' => false,
                    'type' => 'text'
                ]
            )
            ->addColumn(
                'AccountHolder',
                [
                    'header' => __('Account Holder'),
                    'type' => 'text',
                    'index' => 'AccountHolder',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'paymentDate',
                [
                    'header' => __('Payment Date'),
                    'type' => 'datetime',
                    'sortable' => false,
                    'renderer' => DateRenderer::class,
                    'index' => 'paymentDate',
                    'header_css_class' => 'col-date col-date-min-width',
                    'column_css_class' => 'col-date'
                ]
            )
            ->addColumn(
                'paymentAmount',
                [
                    'header' => __('Amount'),
                    'type' => 'id',
                    'filter' => false,
                    'sortable' => false,
                    'index' => 'paymentAmount',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'cardInfo',
                [
                    'header' => __('Payment Method'),
                    'type' => 'text',
                    'sortable' => false,
                    'filter' => false,
                    'index' => 'cardInfo',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'source',
                [
                    'header' => __('Source'),
                    'type' => 'text',
                    'index' => 'source',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'TransactionType',
                [
                    'header' => __('Transaction Type'),
                    'type' => 'text',
                    'index' => 'TransactionType',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'Status',
                [
                    'header' => __('Status'),
                    'type' => 'text',
                    'index' => 'Status',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'resultStatus',
                [
                    'header' => __('Result'),
                    'renderer' => ResultRenderer::class,
                    'type' => 'text',
                    'sortable' => false,
                    'filter' => false,
                    'index' => 'resultStatus',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            );
            $this->addColumn(
                'actions',
                [
                    'header' => __('Actions'),
                    'type' => 'select',
                    'renderer' => SelectRenderer::class,
                    'resizeEnabled' => true,
                    'resizeDefaultWidth' => 10,
                    'is_system' => true,
                    'filter' => false,
                    'id' => 'refNum',
                    'sortable' => false,
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select',
                ]
            );
            $this->addExportType('*/*/exportpaymenthistorycsv', __('CSV'));
            $this->addExportType('*/*/exportpaymenthistoryexl', __('Excel XML'));
            $block = $this->getLayout()->getBlock('grid.bottom.links');

        } catch (Exception $e) {
            /** Exception occured  */
            $this->_ebizchargeLogger->addCritical(
                __("Exception occured during feching Payment History from Ebizcharge Gateway Exception: " .
                    $e->getMessage())
            );
            return parent::_prepareColumns();
        }

        /** block */
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Set Filter values
     *
     * @param mixed $data
     * @return $this|Grid
     * @throws Exception
     */
    protected function _setFilterValues($data)
    {
        $customerId = false;
        $paymentDate = false;
        /**
         * Iterating the Columns
         * @var  $columnId
         * @var  $column
         */
        foreach ($this->getColumns() as $columnId => $column) {

            if (isset(
                $data[$columnId]
            ) && (is_array(
                $data[$columnId]
            ) && !empty($data[$columnId]) || strlen(
                $data[$columnId]
            ) > 0) && $column->getFilter()
            ) {
                $column->getFilter()->setValue($data[$columnId]);
                $this->_addColumnFilterToCollection($column);

                $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();

                if ($field === 'customerId') {
                    $customerId = $data[$columnId];
                }
                if ($field === 'paymentDate' && isset($data[$columnId]['from'])) {
                    $paymentDate = $data[$columnId]['from'];
                }
            }
        }
        /** @var $recurringPayments */
        $recurringPayments = $this->getAllSearchRecurringPayments($customerId, $paymentDate);

        /** @var  $collection */
        $collection = $this->_paymentHistorycollection->addDataToCollection(
            $this->_paymentHistorycollection,
            $recurringPayments
        );

        $this->setCollection($collection);

        return $this;
    }

    /**
     * Add Column Filter To Collection
     *
     * @param mixed $column
     * @return $this|Grid
     * @throws LocalizedException
     * @phpcs:disable
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                /**  START WITH CODE FOR A NEW WAY OF SEARCHING
                 * - if you look in app/code/core/Mage/Adminhtml/Block/Widget/Grid/Column/Filter/Abstract.php
                 * - you'll see that ->getCondition(); returns an array:
                 * public function getCondition()
                 * {
                 *      return array('like'=>'%'.$this->_escapeValue($this->getValue()).'%');
                 * }
                 * - and _escapeValue returns:
                 * protected function _escapeValue($value)
                 * {
                 *     return str_replace('_', '\_', $value);
                 * }
                 * - so we'll "unescape" it in case where we have [$cond['eq'] and unset($cond['like']);]
                 */
                if ($field && isset($cond)) {
                //    if ($this->getAction() instanceof Mage_Adminhtml_Sales_OrderController) {
                        if (isset($cond['like'])) {

                            //cover case where like should go to equal; else means that we have at least one * so like should stay...
                            if (!(substr($cond['like'], -2) === '*%') &&
                                !(substr($cond['like'], 0, 2) === '%*')) {
                                $cond['eq'] = str_replace('%', '', $cond['like']);
                                $cond['eq'] = str_replace('\_', '_', $cond['eq']); //This line was added
                                // with new revision
                                unset($cond['like']);
                            } else {
                                if (substr($cond['like'], 0, 2) !== '%*') {
                                    $cond['like'] = substr($cond['like'], 1);
                                } else {
                                    $cond['like'] = '%' . substr($cond['like'], 2);
                                }
                                if (substr($cond['like'], -2) !== '*%') {
                                    $cond['like'] = substr($cond['like'], 0, -1);
                                } else {
                                    $cond['like'] = substr($cond['like'], 0, -2) . '%';
                                }
                            }
                        }
                  //  }
                    /*END WITH CODE FOR A NEW WAY OF SEARCHING*/
                    $this->getCollection()->addFieldToFilter($field, $cond);
                }
            }
        }
        return $this;
    }
    // phpcs:enable

    /**
     * Prepare Mass Actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('massActionField');
        $this->getMassactionBlock()->setFormFieldName('massActionField');
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'email',
            [
                'label' => __('Send Email'),
                'url' => $this->escapeUrl($this->getUrl(
                    'ebizcharge_ebizcharge/recurrings/emailbulkaction',
                    ['_current' => true, '_use_rewrite' => true]
                )),
                'confirm' => __("Are you sure you want to send email?"),
            ]
        );
        return $this;
    }
}

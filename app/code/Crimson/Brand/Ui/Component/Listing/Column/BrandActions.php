<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/10/2019
 */
namespace Crimson\Brand\Ui\Component\Listing\Column;

class BrandActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path to edit
     *
     * @var string
     */
    const GRID_URL_PATH_EDIT = 'brand/brand/edit';

    /**
     * Url path to delete
     *
     * @var string
     */
    const GRID_URL_PATH_DELETE = 'brand/brand/delete';

    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * constructor
     *
     * @param \Magento\Framework\UrlInterface                              $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory
     * @param \Magento\Framework\AuthorizationInterface                    $authorization
     * @param array                                                        $components
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->authorization = $authorization;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!$this->_isAllowed()) {
            return $dataSource;
        }

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['brand_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::GRID_URL_PATH_EDIT,
                                [
                                    'brand_id' => $item['brand_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::GRID_URL_PATH_DELETE,
                                [
                                    'brand_id' => $item['brand_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.name }"'),
                                'message' => __('Are you sure that you want to delete the Brand "${ $.$data.name }"?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }

    /**
     * Prepare component configuration
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->_isAllowed()) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    protected function _isAllowed()
    {
        return $this->authorization->isAllowed('Crimson_Brand::edit');
    }
}

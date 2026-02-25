<?php

namespace Crimson\Testimonial\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;

class CategoryActions extends Column
{
    /**
 * Url path
*/
    const MENU_URL_PATH_EDIT   = 'testimonial/category/edit';
    const MENU_URL_PATH_DELETE = 'testimonial/category/delete';

    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::MENU_URL_PATH_EDIT
    ) {
        $this->urlBuilder       = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl          = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['category_id'])) {
                    $item[$name]['edit'] = [
                                            'href'  => $this->urlBuilder->getUrl($this->editUrl, ['category_id' => $item['category_id']]),
                                            'label' => __('Edit'),
                                           ];
                    /*
                        $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::MENU_URL_PATH_DELETE, ['category_id' => $item['category_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                        'title' => __('Delete ${ $.$data.title }'),
                        'message' => __('Are you sure you wan\'t to delete a ${ $.$data.title } record?')
                        ]
                    ];*/
                }
            }
        }

        return $dataSource;
    }
}

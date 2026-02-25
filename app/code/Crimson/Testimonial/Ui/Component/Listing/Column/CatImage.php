<?php

namespace Crimson\Testimonial\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class CatImage extends Column
{
    const NAME = 'thumbnail';

    const ALT_FIELD = 'name';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper   = $imageHelper;
        $this->urlBuilder    = $urlBuilder;
        $this->_storeManager = $storeManager;
    }


    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if(!isset($item['image'])) { continue;
                }

                if($item['image']) {
                    $thumbnailUrl            = $path.$item['image'];
                    $item[$fieldName.'_src'] = $thumbnailUrl;
                    $item[$fieldName.'_alt'] = __('Avatar Preview');
                    $item[$fieldName.'_link']     = $this->urlBuilder->getUrl(
                        'testimonial/testimonial/edit',
                        [
                         'testimonial_id' => $item['testimonial_id'],
                         'store'          => $this->context->getRequestParam('store'),
                        ]
                    );
                    $item[$fieldName.'_orig_src'] = $thumbnailUrl;
                }
            }
        }

        return $dataSource;
    }


    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}

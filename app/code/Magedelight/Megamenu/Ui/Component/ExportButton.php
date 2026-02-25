<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\AbstractComponent;

class ExportButton extends AbstractComponent
{
    /**
     * Component name
     */
    private const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * ExportButton Construct
     *
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');

        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                if ($option['value'] == 'xml') {
                    $option['label'] = __('Export to XML');
                    $option['url'] = $this->urlBuilder->getUrl('megamenu/sampleimport/export');
                    $options[] = $option;
                }
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}

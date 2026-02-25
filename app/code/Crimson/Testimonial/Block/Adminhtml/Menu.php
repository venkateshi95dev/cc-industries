<?php

namespace Crimson\Testimonial\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class Menu extends Template
{
    /**
     * @var null|array
     */
    protected $items = null;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Crimson_Testimonial::menu.phtml';


    public function __construct(
        Context $context,
        array   $data = []
    ) {
        parent::__construct($context);
    }

    public function getMenuItems()
    {
        if ($this->items === null) {
            $items = [
                      'testimonial' => [
                            'title' => 'Manage Testimonials',
                            'url' => $this->getUrl('*/testimonial/index'),
                            'resource' => 'Crimson_Testimonial::testimonial',
                            'child' => [
                                'testimonial/new/' => [
                                    'title' => 'New Testimonial',
                                    'url' => $this->getUrl('*/testimonial/new/'),
                                    'resource' => 'Crimson_Testimonial::testimonial_edit',
                                ]
                            ]
                        ],
                        'category' => [
                            'title' => 'Manage Categories',
                            'url' => $this->getUrl('*/category/index'),
                            'resource' => 'Crimson_Testimonial::category',
                            'child' => [
                                'category/new' => [
                                    'title' => 'New Category',
                                    'url' => $this->getUrl('*/category/new'),
                                    'resource' => 'Crimson_Testimonial::category_edit',
                                ]
                            ]
                        ],
                      'settings' => [
                                     'title'    => 'Settings',
                                     'url'      => $this->getUrl('adminhtml/system_config/edit/section/testimonial'),
                                     'resource' => 'Crimson_Testimonial::config_testimonial',
                                    ],
                     ];
            foreach ($items as $index => $item) {
                if (array_key_exists('resource', $item)) {
                    if (!$this->_authorization->isAllowed($item['resource'])) {
                        unset($items[$index]);
                    }
                }
            }

            $this->items = $items;
        }

        return $this->items;
    }


    /**
     * @return array
     */
    public function getCurrentItem()
    {
        $items          = $this->getMenuItems();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName     = $this->getRequest()->getActionName();

        $key = $controllerName . '/' . $actionName;
        if (array_key_exists($key, $items)) {
            return $items[$key];
        }

        if (array_key_exists($controllerName, $items)) {
            return $items[$controllerName];
        }

        return $items['page'];
    }


    /**
     * @param array $item
     * @return string
     */
    public function renderAttributes(array $item)
    {
        $result = '';
        if (isset($item['attr'])) {
            foreach ($item['attr'] as $attrName => $attrValue) {
                $result .= sprintf(' %s=\'%s\'', $attrName, $attrValue);
            }
        }

        return $result;
    }


    /**
     * @param $itemIndex
     * @return bool
     */
    public function isCurrent($itemIndex)
    {
        $controllerName = $this->getRequest()->getControllerName();
        $actionName     = $this->getRequest()->getActionName();
        $key = $controllerName . '/' . $actionName;
        if ($key == $itemIndex) {
            return true;
        }
        return $itemIndex == $this->getRequest()->getControllerName();
    }
}

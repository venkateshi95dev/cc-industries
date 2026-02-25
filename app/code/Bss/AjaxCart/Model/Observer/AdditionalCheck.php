<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AjaxCart\Model\Observer;

use Bss\AjaxCart\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AdditionalCheck implements ObserverInterface
{
    /**
     * Ajax cart helper.
     *
     * @var Data
     */
    protected $helper;

    /**
     * Http request.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Initialize dependencies.
     *
     * @param Data $helper
     * @param RequestInterface $request
     */
    public function __construct(
        Data             $helper,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Check is show additional data in quick view.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $layout = $observer->getLayout();
        $block = $layout->getBlock('product.info.details');
        if ($block && $this->request->getModuleName() == 'ajaxcart') {
            $isShow = $this->helper->isShowQuickviewAddData();

            if (!$isShow) {
                $layout->unsetElement('product.info.details');
            }
        }
    }
}

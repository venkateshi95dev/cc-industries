<?php

namespace Crimson\Testimonial\Controller\Adminhtml\Testimonial;

use Magento\Catalog\Controller\Adminhtml\Product;

class Products extends Product
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;


    /**
     * @param \Magento\Backend\App\Action\Context                   $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory          $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }


    /**
     * Get crosssell products grid and serializer block
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {

        $id          = $this->getRequest()->getparam('testimonial_id');
        $testimonial = $this->_objectManager->create('Crimson\Testimonial\Model\Testimonial');
        $testimonial->load($id);
        $registry = $this->_objectManager->get('Magento\Framework\Registry');
        $registry->register("current_testimonial", $testimonial);

        $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('testimonial.testimonial.edit.tab.products')
            ->setProductsCrossSell($this->getRequest()->getPost('testimonial_products', null));
        return $resultLayout;
    }
}

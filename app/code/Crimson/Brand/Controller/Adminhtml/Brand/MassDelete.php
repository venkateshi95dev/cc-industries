<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/11/2019
 */
namespace Crimson\Brand\Controller\Adminhtml\Brand;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Crimson_Brand::edit';

    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * Collection Factory
     *
     * @var \Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Crimson\Brand\Api\BrandRepositoryInterface
     */
    protected $_brandRepositoryInterface;

    /**
     * constructor
     *
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Crimson\Brand\Model\ResourceModel\Brand\CollectionFactory $collectionFactory,
        \Crimson\Brand\Api\BrandRepositoryInterface $brandRepositoryInterface,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_brandRepositoryInterface = $brandRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $delete = 0;
        foreach ($collection as $brand) {
            $this->_brandRepositoryInterface->delete($brand);
            $delete++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}

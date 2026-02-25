<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/17/2019
 */
namespace Crimson\Brand\Controller\Adminhtml\Brand;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Message\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Crimson\Brand\Api\BrandRepositoryInterface;
use Crimson\Brand\Api\Data\BrandInterface;
use Crimson\Brand\Api\Data\BrandInterfaceFactory;
use Crimson\Brand\Controller\Adminhtml\Brand;
use Crimson\Brand\Model\Uploader;
use Crimson\Brand\Model\UploaderPool;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends Brand
{
    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Crimson_Brand::edit';

    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var BrandRepositoryInterface
     */
    protected $brandRepository;

    /**
     * @var BrandInterfaceFactory
     */
    protected $brandFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * @var DateTime $date
     */
    protected $date;

    /**
     * Save constructor.
     *
     * @param Registry $registry
     * @param BrandRepositoryInterface $brandRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Manager $messageManager
     * @param BrandInterfaceFactory $brandFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param UploaderPool $uploaderPool
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        BrandRepositoryInterface $brandRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Manager $messageManager,
        BrandInterfaceFactory $brandFactory,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool,
        DateTime $date,
        Context $context
    ) {
        parent::__construct($registry, $brandRepository, $resultPageFactory, $dateFilter, $context);
        $this->messageManager   = $messageManager;
        $this->brandFactory     = $brandFactory;
        $this->brandRepository  = $brandRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->uploaderPool     = $uploaderPool;
        $this->date             = $date;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('brand_id');
            if ($id) {
                $model = $this->brandRepository->getById($id);
                $data['updated_at'] = $this->date->gmtDate();
            } else {
                unset($data['brand_id']);
                $model = $this->brandFactory->create();
                $data['created_at'] = $this->date->gmtDate();
                $data['updated_at'] = $this->date->gmtDate();
            }

            try {
                $largeImage = $this->getUploader('image')->uploadFileAndGetName('large_image', $data);
                $data['large_image'] = $largeImage;
                $smallImage = $this->getUploader('image')->uploadFileAndGetName('small_image', $data);
                $data['small_image'] = $smallImage;

                $this->dataObjectHelper->populateWithArray($model, $data, BrandInterface::class);
                $this->brandRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved this Brand.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['brand_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the Brand:' . $e->getMessage())
                );
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['brand_id' => $this->getRequest()->getParam('brand_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $type
     * @return Uploader
     * @throws \Exception
     */
    protected function getUploader($type)
    {
        return $this->uploaderPool->getUploader($type);
    }
}

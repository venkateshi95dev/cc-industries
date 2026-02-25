<?php

namespace Silk\Coker\Helper;

use Crimson\CokerWV\Api\CokerStoreInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const NEWSLETTER = 'finderattribute/newsletter/is_active';
    const EMAIL = 'finderattribute/cartreport/email';
    const EMAILID = 'finderattribute/cartreport/emailid';
    const MESSAGE = 'finderattribute/checkout/message';
    const FINDER_NO_RESULTS_BLOCK = 'finderattribute/finder/finder_no_results_block';
    const SEARCH_NO_RESULTS_BLOCK = 'finderattribute/usual/search_no_results_block';
    const MESSAGE_SHIPPING_ADDRESS_NOTICE = 'finderattribute/checkout/shipping_address_notice';
    const PRODUCT_EWP_ALLOWED_ATTRIBUTE_SET_NAME = 'product_modal/product/ewp_allowed_attribute_set_name';

    protected $_layout;
    private $transportBuilder;
    protected $scopeConfig;
    protected $fileFactory;
    protected $csvProcessor;
    protected $directoryList;
    protected $file;

    public function __construct(
        private readonly CategoryFactory $categoryFactory,
        private readonly \Magento\Catalog\Model\ProductFactory $productFactory,
        private readonly \Magento\Framework\Registry $registry,
        private readonly StoreRepositoryInterface    $storeRepositoryInterface,
        LayoutInterface $layout,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        FileFactory $fileFactory,
        Csv $csvProcessor,
        DirectoryList $directoryList,
        File $file,
        Context $context
    ) {
        $this->_layout = $layout;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->file = $file;
        parent::__construct($context);
    }

    public function getRimCategoryId()
    {
        $rimCat = $this->categoryFactory->create();
        $rimCat = $rimCat->loadByAttribute('url_path', 'wheels/rims');
        return $rimCat->getId();
    }
    public function InCategory($productId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $this->registry->registry('current_category');
        if ($category && $category->getId()) {
            $id = $category->getId();
            $product = $this->productFactory->create()->load($productId);
            $ids = $product->getCategoryIds();

            if (!in_array($id, $ids)) {
                return false;
            }
            return true;
        }
        return true;
    }
    public function isShowNewsletter()
    {
        return $this->scopeConfig->getValue(self::NEWSLETTER, ScopeInterface::SCOPE_WEBSITE);
    }

    public function sendEmail($fileName, $objs): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/silk_sendemail.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('start to send abandoned email');

        $store = $this->storeRepositoryInterface->get(CokerStoreInterface::COKER_STORE_CODE);
        $sender = [
            'name' => 'Coker Tire Co',
            'email' => 'support@coker.com',
        ];

        $to = $this->scopeConfig->getValue(self::EMAIL, ScopeInterface::SCOPE_WEBSITE, $store->getWebsiteId());
        $to = $this->trimall($to ?? '');
        $emails = explode(",", $to);
        $templateId = $this->scopeConfig->getValue(self::EMAILID, ScopeInterface::SCOPE_WEBSITE, $store->getWebsiteId());
        $filePath = $this->directoryList->getPath(DirectoryList::MEDIA) . "/abandoned/" . $fileName;
        $logger->info('file path:' . $filePath);
        try {
            if (file_exists($filePath)) {
                $mimeType = mime_content_type($filePath);
                foreach ($emails as $key => $value) {
                    $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                        ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $store->getId()])
                        ->addAttachment($this->file->read($filePath), $fileName, $mimeType)
                        ->setTemplateVars(array("items" => $objs))
                        ->setFrom($sender)
                        ->addTo($value)
                        ->getTransport();
                    $transport->sendMessage();
                }
            }
        } catch (\Exception $e) {
            $logger->info('send email fail:' . $e->getMessage());
        }
    }

    public function trimall($str)
    {
        $oldchar = array(" ", "　", "\t", "\n", "\r");
        $newchar = array("", "", "", "", "");
        return str_replace($oldchar, $newchar, $str);
    }

    public function getCheckoutMessage()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $message =  $this->scopeConfig->getValue(self::MESSAGE, $storeScope);
        return '<div class="checkoutmessage" style="display:none; color:red">' . $message . '</div>';
    }

    public function importDatatoCsv($data): string
    {
        $cartData = $this->getCartData($data);
        $now = new \DateTime();
        $nowDay = $now->format('Y-m-d');
        $fileName = $nowDay . '-abandoned-cart.csv';
        $filePath = $this->directoryList->getPath(DirectoryList::MEDIA) . "/abandoned/" . $fileName;
        $this->csvProcessor->saveData($filePath, $cartData);
        $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => 'abandoned/' . $fileName,
            ],
            DirectoryList::MEDIA,
            'application/octet-stream'
        );

        return $fileName;
    }

    public function getCartData($objs): array
    {
        $result = [];
        $result[] = [
            'email',
            'customer_name',
            'country_code',
            'telephone',
            'street',
            'product name',
            'product sku',
            'product price',
            'product qty',
            'product total',
        ];
        foreach ($objs as $key => $value) {
            $result[] = [
                $value['email'],
                $value['customer_name'],
                $value['country_code'],
                $value['telephone'],
                $value['street'],
                null,
                null,
                null,
                null,
                null,

            ];
            foreach ($value["prods"] as $key => $val) {
                $result[] = [
                    null,
                    null,
                    null,
                    null,
                    null,
                    $val['name'],
                    $val['sku'],
                    str_replace(",", "", $val['price']),
                    $val['qty'],
                    str_replace(",", "", $val['total']),

                ];
            }
        }

        return $result;
    }
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    public function getFinderNoResultsBlock()
    {
        return $this->getStoreConfig(self::FINDER_NO_RESULTS_BLOCK);
    }

    public function getSearchNoResultsBlock()
    {
        $blockId = $this->getStoreConfig(self::SEARCH_NO_RESULTS_BLOCK);
        $html = $this->getBlock($blockId);

        return $html;
    }

    public function getBlock($identifier = '')
    {
        return $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId($identifier)->toHtml();
    }

    public function getShippingAddressNotice()
    {
        return $this->getStoreConfig(self::MESSAGE_SHIPPING_ADDRESS_NOTICE);
    }

    public function getProductEP($type)
    {
        $json = $this->getStoreConfig('product_modal/product/' . $type);
        if ($array = json_decode($json, true)) {
            return $array;
        }
    }


    public function getProductEwpAllowedAttributeSetName()
    {
        return $this->getStoreConfig(self::PRODUCT_EWP_ALLOWED_ATTRIBUTE_SET_NAME);
    }
}

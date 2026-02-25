<?php
declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\State;
use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CreateGuestOrderEmailTemplate implements DataPatchInterface
{
    public function __construct(
        private TemplateFactory $templateFactory,
        private WriterInterface $configWriter,
        private State $appState,
        private StoreManagerInterface $storeManager,
        private LoggerInterface $logger
    ) {}

    public function apply() : void
    {
        $this->appState->emulateAreaCode('frontend', function () {
            try {
                $store = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE);
                $storeId = (int)$store->getId();

                $templateFile = __DIR__ . '/../../data/email/new_order_cc.html';
                if (!file_exists($templateFile)) {
                    throw new \RuntimeException("Template file not found: $templateFile");
                }

                $template = $this->templateFactory->create();
                $templateData = [
                    'template_code' => 'Order Confirmation Guest- CC',
                    'template_text' => file_get_contents($templateFile),
                    'template_type' => 2,
                    'template_subject' => 'Your {{var store.getFrontendName()}} order confirmation',
                    'orig_template_code' => 'sales_email_order_template',
                    'orig_template_variables' => json_encode([
                        'var formattedBillingAddress' => 'Billing Address',
                        'var formattedShippingAddress' => 'Shipping Address',
                        'var order.getEmailCustomerNote()' => 'Customer Note',
                        'var order.increment_id' => 'Order ID',
                        'var order.getCreatedAtFormated("long")' => 'Order Date',
                        'var order.getCustomerName()' => 'Customer Name',
                        'var order.getShippingDescription()' => 'Shipping Method',
                        'var payment_html' => 'Payment Details',
                        'var store.getFrontendName()' => 'Store Name',
                        'var order' => 'Order Data Object',
                        'var comment' => 'Order Comment',
                    ], JSON_PRETTY_PRINT)
                ];

                $template->setData($templateData)->save();

                $this->configWriter->save(
                    'sales_email/order/guest_template',
                    $template->getId(),
                    'stores',
                    $storeId
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return;
            }
        });
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

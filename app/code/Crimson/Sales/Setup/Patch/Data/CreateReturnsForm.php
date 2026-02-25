<?php

declare(strict_types=1);

namespace Crimson\Sales\Setup\Patch\Data;

use Amasty\Customform\Api\FormRepositoryInterface;
use Amasty\Customform\Model\Form;
use Amasty\Customform\Model\FormFactory;
use Crimson\Sales\Setup\Patch\Data\CreateReturnFormSuccessPage;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class CreateReturnsForm implements DataPatchInterface
{
    const JSON_FORM = '/../../data/returns-form.json';
    const FORM_CODE = 'returns_form';

    public function __construct(
        protected FormFactory $formFactory,
        protected FormRepositoryInterface $formRepository,
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected StoreRepositoryInterface $storeRepository,
        protected State $appState,
        protected LoggerInterface $logger
    ) {}

    /**
     * @return string[]
     */
    public static function getDependencies() : array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases() : array
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->startSetup();

        try {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        try {
            $formContent = $this->getFileContent(__DIR__ . self::JSON_FORM);
            $store = $this->storeRepository->get('default');

            $form = $this->formFactory->create();
            $form->setTitle("Returns");
            $form->setCode(self::FORM_CODE);
            $form->setStatus(Form::STATUS_ENABLED);
            $form->setCustomerGroup(32000);
            $form->setStoreId($store->getId() ?? 1);
            $form->setSendNotification(1);
            $form->setSendTo("customerservice@zip-corvette.com");
            $form->setEmailTemplate("amasty_customform_email_template");
            $form->setSubmitButton("Submit");
            $form->setSuccessUrl(CreateReturnFormSuccessPage::IDENTIFIER);
            $form->setSuccessMessage("Thanks for contacting us. Your request was saved successfully.");
            $form->setFormJson($formContent);
            $form->setEmailFieldHide(true);
            $form->setPopupShow(false);
            $form->setFormTitle('["Returns"]');
            $form->setAutoReplyTemplate("amasty_customform_autoresponder_with_submited_fields_template");
            $form->setFormContainsSensitiveData(false);
            $form->setIsAutoReplyEnabled(true);
            $form->setIsSurveyModeEnabled(false);
            $form->setIsVisible(true);

            $this->formRepository->save($form);

        } catch (Exception $e) {
            $this->logger->error("Wasn't able to apply Data Patch 'CreateReturnsForm'.");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function getFileContent(string $path): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return (string)file_get_contents($path);
    }

}

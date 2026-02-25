<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\PageBuilder\Model\TemplateRepository;

class AddGridsTemplate implements DataPatchInterface
{
    const CMS_TEMPLATES = [
        [
            'name' => 'C.Central Spiral Grid Template',
            'path' => '/../../data/templates/spiral-grid.html',
            'image' => 'spiral-grid.jpg',
        ],
        [
            'name' => 'C.Central Square Grid Template',
            'path' => '/../../data/templates/square-grid.html',
            'image' => 'square-grid.jpg',
        ]
    ];

    const CMS_TEMPLATE_SECTION = 'page';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly TemplateRepository       $templateRepository,
        private readonly TemplateFactory $templateFactory
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws Exception
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        foreach (self::CMS_TEMPLATES as $templateData) {
            $filePath = __DIR__ . $templateData['path'];
            $content = $this->getHtmlContent($filePath);

            $template = $this->templateFactory->create();
            $template->setName($templateData['name'])
                ->setTemplate($content)
                ->setPreviewImage('wysiwyg/central/' . $templateData['image'])
                ->setCreatedFor(self::CMS_TEMPLATE_SECTION);

            $this->templateRepository->save($template);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    protected function getHtmlContent($path): false|string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new Exception("File not found `$path`");
        }
        return file_get_contents($path);
    }
}

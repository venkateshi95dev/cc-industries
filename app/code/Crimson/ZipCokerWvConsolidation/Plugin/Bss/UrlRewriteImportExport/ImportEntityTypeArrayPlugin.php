<?php
namespace Crimson\ZipCokerWvConsolidation\Plugin\Bss\UrlRewriteImportExport;

class ImportEntityTypeArrayPlugin extends \Magento\ImportExport\Model\Source\Import\Entity
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * EntityTypeArrayPlugin constructor.
     * @param \Magento\ImportExport\Model\Import\ConfigInterface $importConfig
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\ImportExport\Model\Import\ConfigInterface $importConfig,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        parent::__construct($importConfig);
    }

    /**
     * @param object $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundToOptionArray($subject, $proceed)
    {
        $bssOptions = [];
        $bssOptions[] = ['label' => __('-- Please Select --'), 'value' => ''];
        $options = [];
        $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        foreach ($this->_importConfig->getEntities() as $entityName => $entityConfig) {
            if (strpos($entityName, 'bss')!==false) {
                $bssOptions[] = ['label' => __($entityConfig['label']), 'value' => $entityName];
            } else {
                $options[] = ['label' => __($entityConfig['label']), 'value' => $entityName];
            }
        }

        if (strpos($this->request->getFullActionName(), 'bss')!==false) {
            return $bssOptions;
        }
        return $options;
    }
}

<?php
namespace Crimson\ZipCokerWvConsolidation\Plugin\Bss\UrlRewriteImportExport;

class ExportEntityTypeArrayPlugin extends \Magento\ImportExport\Model\Source\Export\Entity
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * EntityTypeArrayPlugin constructor.
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        parent::__construct($exportConfig);
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
        foreach ($this->_exportConfig->getEntities() as $entityName => $entityConfig) {
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

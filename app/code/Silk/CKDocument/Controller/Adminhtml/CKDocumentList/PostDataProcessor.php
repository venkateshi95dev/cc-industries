<?php
namespace Silk\CKDocument\Controller\Adminhtml\CKDocumentList;

/**
 * Controller helper for user input.
 */
class PostDataProcessor
{

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    public function filter(&$data)
    {
        $filterRules = [];

        if (!empty($data['file'])) {
           $file = $data['file'];
           $file = array_shift($file);

           $data['file'] = $file['url'];
           $data['file_type'] = $file['type'];
           $data['file_size'] = $file['size'];
           $data['file_name'] = $file['name'];
        }

        return $data;
    }


}

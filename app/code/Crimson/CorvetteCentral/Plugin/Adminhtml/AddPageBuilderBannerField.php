<?php
namespace Crimson\CorvetteCentral\Plugin\Adminhtml;

use Magento\Cms\Model\Page\DataProvider;

class AddPageBuilderBannerField
{
    public function afterGetData(DataProvider $subject, $result)
    {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item['page_id'])) {
                    if (!isset($item['custom_banner'])) {
                        $item['custom_banner'] = '';
                    }
                }
            }
        }
        return $result;
    }
}

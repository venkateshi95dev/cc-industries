<?php

declare(strict_types=1);

namespace Crimson\Sales\Plugin;

use Amasty\Customform\Api\Data\FormInterface;
use Amasty\Customform\Ui\DataProvider\Modifiers\AddEmbeddingCode;

class EmbeddingCodePlugin
{
    /**
     * @param AddEmbeddingCode $subject
     * @param array $data
     * @param array $result
     * @return array
     */
    public function afterModifyData(AddEmbeddingCode $subject, array $data, array $result) : array
    {
        $data['cms'] = sprintf(
            '{{widget type="Amasty\Customform\Block\Init" template="Crimson_Sales::init.phtml" form_id="%s"}}',
            $data[FormInterface::FORM_ID]
        );

        return $data;
    }
}

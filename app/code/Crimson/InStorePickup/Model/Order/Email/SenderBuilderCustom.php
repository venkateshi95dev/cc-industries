<?php

namespace Crimson\InStorePickup\Model\Order\Email;

use Magento\Sales\Model\Order\Email\SenderBuilder;

class SenderBuilderCustom extends SenderBuilder
{

    public function send()
    {
        $this->configureEmailTemplate();

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

        $templateOptions = $this->templateContainer->getTemplateVars();
        if (!empty($templateOptions['source_email'])) {
            $this->transportBuilder->addBcc($templateOptions['source_email']);
        }

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}

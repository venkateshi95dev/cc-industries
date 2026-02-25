<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Gateway\Http\Client;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Adapter\EbizchargeAdapterFactory;
use Exception;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Http Client Abstract Transaction
 *
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var EbizchargeAdapterFactory
     */
    protected EbizchargeAdapterFactory $adapterFactory;

    /**
     * Main Class Constructor only for Dependency Injection
     *
     * @param EbizchargeAdapterFactory $adapterFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        EbizchargeAdapterFactory $adapterFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;

        /** @var  adapterFactory */
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Place Request
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');

            $this->ebizchargeLogger->addError($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array)$response['object'];
            /** Logging error */
            /**
            $this->ebizchargeLogger->addError(__(
                "Exception occurred process payment request error: ".json_encode($response),
                100,
                $log
            ));
             * **/
        }

        return $response;
    }

    /**
     * Process http request
     *
     * @param array $data
     */
    abstract protected function process(array $data);
}

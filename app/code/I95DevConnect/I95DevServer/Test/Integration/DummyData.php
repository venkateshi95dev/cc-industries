<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration;

/**
 * shipment test case for reverse flows
 */
class DummyData extends DummyDataParent //NOSONAR
{
    /**
     * Read data from json file
     *
     * @createdBy Sravani Polu
     * @param     $fileName
     * @return    false|string
     */
    public function readJsonFile($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }

    /**
     * Adds AES 256 Encryption
     *
     * @parameter string
     * @param     string $string
     * @return    \I95DevConnect\I95DevServer\Model\ServiceMethod\Encrypted String
     */
    public function encryptAES($string = "")
    {
        return $this->abstractServiceMethod->encryptAES($string);
    }

    /**
     * Adds AES 256 Decryption
     *
     * @parameter string
     * @param     string $string
     * @return    Decrypted String
     */
    public function decryptDES($string = '')
    {
        return $this->abstractServiceMethod->decryptDES($string);
    }

    /**
     * Read error message from error Id
     *
     * @param  type $errorId
     * @return string
     * @author Hrusikesh Manna
     */
    public function readErrorMsg($errorId)
    {
        if ($errorId > 0) {
            $errorMsg = $this->errorUpdateData->getCollection()
                ->addFieldToFilter('id', $errorId)
                ->getData();
            $message = $errorMsg[0]['msg'];
        } else {
            $message =  null;
        }
        return $message;
    }

    /**
     * get error message
     *
     * @param  int $errorId
     * @return string
     * @author Divya Koona
     */
    public function getErrorData($errorId)
    {
        $errorData = $this->errorUpdateData->getCollection()
            ->addFieldToFilter('id', $errorId)
            ->getData();
        return $errorData[0]['msg'];
    }

    /**
     * @param  $EntityId
     * @param  $entityName
     * @param  $methodName
     * @return mixed
     */
    public function getEntityInfoData($EntityId, $entityName, $methodName)
    {
        $outboundData = $this->getOutbountMqData($EntityId, $entityName);
        $this->assertNotNull($outboundData);
        $this->assertEquals($EntityId, $outboundData[0]['magento_id'], "Magento id is different");
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $outboundData[0]['status'],
            "Status should be PENDING in outbound message queue"
        );
        $response = $this->i95devServerRepo->serviceMethod(
            $methodName,
            '{"requestData":[],"packetSize":50,"erp_name":"ERP"}'
        );
        $this->assertEquals(true, $response->result, "Unable to fectch " . $entityName . " from outbound MQ");
        $this->assertEquals($outboundData[0]['magento_id'], $response->resultData[0]['sourceId']);
        $inputData = $response->resultData[0]['InputData'];
        $this->assertNotEmpty($inputData);
        $responseData = json_decode($this->decryptDES($inputData), 1);
        $this->assertNotEmpty($responseData);

        return $responseData;
    }

    /**
     * Get outbound message queue collection data by magento_id
     *
     * @param $magentoId
     *
     * @return array
     * @author Debashis S. Gopal
     */
    public function getOutbountMqData($magentoId, $entityName)
    {
        $collections = $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', $entityName)
            ->addFieldToFilter("magento_id", $magentoId);
        return $collections->getData();
    }

        /**
         * get inbound message queue collection by ref name
         *
         * @param  $ref_name
         * @return Object
         * @author Arushi Bansal
         */
    public function getInboundMqData($ref_name)
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', $ref_name)
            ->getData();
    }
}

<?php

namespace IWD\AddressValidation\Model\Usps;

use IWD\AddressValidation\Model\XML\XMLParser;

/**
 * USPS Base class
 * used to perform the actual api calls
 * @since 1.0
 * @author Vincent Gabriel
 */
class USPSBaseRest extends USPSBase
{
    /**
     * Live API Url
     */
    const LIVE_API_URL = 'https://api.usps.com/';

    /**
     * Test API Url
     */
    const TEST_API_URL = 'https://api-cat.usps.com/';

    /**
     * @var
     */
    public $apiKey;

    /**
     * @var
     */
    public $secretKey;

    /**
     * @var
     */
    public $access_token;

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }


    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }


    /**
     * Return the post data fields as an array
     * @return array
     */
    public function getPostData()
    {
        return $this->addresses;
    }

    protected function doRequest($ch = null)
    {
        if (!$ch) {
            $ch = curl_init();
        }

        $this->getAuthKey();

        $request =  self::$testMode ? self::TEST_API_URL.'addresses/v3/address?' : self::LIVE_API_URL.'addresses/v3/address?';

        curl_setopt_array($ch, array(
            CURLOPT_URL => $request . http_build_query($this->getPostData(), '', '&'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer ' . $this->access_token
            ),
        ));

            // execute
        $this->setResponse(curl_exec($ch));
        $this->setHeaders(curl_getinfo($ch));

        // fetch errors
        $this->setErrorCode(curl_errno($ch));
        $this->setErrorMessage(curl_error($ch));

        // Convert response to arrayUrbanization
        $this->convertResponseToArray();

        // If it failed then set error code and message
        if ($this->isError()) {
            $arrayResponse = $this->getArrayResponse();
            // Find the error number
            $errorInfo = $this->getValueByKey($arrayResponse, 'Error');

            if ($errorInfo) {
                $this->setErrorCode($errorInfo['Number']);
                $this->setErrorMessage($errorInfo['Description']);
            }
        }

        // close
        curl_close($ch);

        return $this->getResponse();
    }

    /**
     * @return void
     * @throws \Safe\Exceptions\JsonException
     */
    public function getAuthKey()
    {
        $ch = curl_init();

        $host =  self::$testMode ? self::TEST_API_URL.'oauth2/v3/token' : self::LIVE_API_URL.'oauth2/v3/token';

        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \Safe\json_encode($this->getKeys()));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseRaw = curl_exec($ch);
        $response = json_decode($responseRaw, true);

        if(isset($response['access_token'])){
            $this->access_token = $response['access_token'];
        }

        curl_close($ch);
    }

    function getKeys()
    {
        if (empty($this->apiKey) || empty($this->secretKey)) {
            throw new LocalizedException(__("Empty USPS API keys."));
        }

        return ['client_id' => $this->apiKey, 'client_secret' => $this->secretKey, 'grant_type' => 'client_credentials'];
    }
    /**
     * Return the xml string built that we are about to send over to the api
     * @return string
     */
    protected function getXMLString()
    {
        // Add in the defaults
        $postFields = [
            '@attributes' => ['USERID' => $this->username],
        ];

        // Add in the sub class data
        $postFields = array_merge($postFields, $this->getPostFields());

        $xml = XMLParser::createXML($this->apiCodes[$this->apiVersion], $postFields);
        return $xml->saveXML();
    }

    /**
     * Did we encounter an error?
     * @return boolean
     */
    public function isError()
    {
        $headers = $this->getHeaders();
        $response = $this->getArrayResponse();

        // Make sure the response does not have error in it
        if (isset($response['Error'])) {
            return true;
        }

        // Check to see if we have the Error word in the response
        if (strpos($this->getResponse(), '<Error>') !== false) {
            return true;
        }

        // No error
        return false;
    }

    /**
     * Was the last call successful
     * @return boolean
     */
    public function isSuccess()
    {
        return !$this->isError() ? true : false;
    }

    /**
     * Return the response represented as string
     * @return array
     */
    public function convertResponseToArray()
    {
        if ($this->getResponse()) {
            $this->setArrayResponse(\GuzzleHttp\json_decode($this->getResponse(), true));
        }

        return $this->getArrayResponse();
    }

    /**
     * Set the array response value
     * @param array $value
     * @return void
     */
    public function setArrayResponse($value)
    {
        $this->arrayResponse = $value;
    }

    /**
     * Return the array representation of the last response
     * @return array
     */
    public function getArrayResponse()
    {
        return $this->arrayResponse;
    }

    /**
     * Set the response
     *
     * @param mixed the response returned from the call
     * @return $this
     */
    public function setResponse($response = '')
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the response data
     *
     * @return mixed the response data
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $headers
     * @return $this
     */
    public function setHeaders($headers = '')
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get the headers
     *
     * @return array the headers returned from the call
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setErrorCode($code = 0)
    {
        $this->errorCode = $code;
        return $this;
    }

    /**
     * Get the error code number
     *
     * @return integer error code number
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setErrorMessage($message = '')
    {
        $this->errorMessage = $message;
        return $this;
    }

    /**
     * Get the error code message
     *
     * @return string error code message
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Find a key inside a multi dim. array
     * @param array $array
     * @param string $key
     * @return mixed
     */
    protected function getValueByKey($array, $key)
    {
        foreach ($array as $k => $each) {
            if ($k == $key) {
                return $each;
            }

            if (is_array($each)) {
                if ($return = $this->getValueByKey($each, $key)) {
                    return $return;
                }
            }
        }

        // Nothing matched
        return null;
    }
}

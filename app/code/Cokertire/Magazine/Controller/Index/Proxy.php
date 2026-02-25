<?php

namespace Cokertire\Magazine\Controller\Index;

use Magento\Framework\App\Action\Context;

class Proxy extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    const CSAJAX_FILTERS = true;
    const CSAJAX_FILTER_DOMAIN = false;
    const CSAJAX_DEBUG = false;

    public function __construct(Context $context,
     \Magento\Framework\Registry $registry,
     \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
     \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory,
     \Magento\Framework\View\Result\PageFactory $resultPageFactory
     )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeInterface;
        $this->resultJsonFactory            = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $valid_requests = array(
            'http://dev.cokertire.local',
            'http://staff.coker.local/trackvia/apiAdd/',
            'https://www.cokertire.com',
            'http://staff.coker.com/trackvia/apiAdd/',
            'https://staging.cokertire.com/'
        );

        /* * * STOP EDITING HERE UNLESS YOU KNOW WHAT YOU ARE DOING * * */

        // identify request headers
        $request_headers = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0  ||  strpos($key, 'CONTENT_') === 0) {
                $headername = str_replace('_', ' ', str_replace('HTTP_', '', $key));
                $headername = str_replace(' ', '-', ucwords(strtolower($headername)));
                if (!in_array($headername, array( 'Host', 'X-Proxy-Url' ))) {
                    $request_headers[] = "$headername: $value";
                }
            }
        }

        // identify request method, url and params
        $request_method = $_SERVER['REQUEST_METHOD'];
        if ('GET' == $request_method) {
            $request_params = $_GET;
        } elseif ('POST' == $request_method) {
            $request_params = $_POST;
            if (empty($request_params)) {
                $data = file_get_contents('php://input');
                if (!empty($data)) {
                    $request_params = $data;
                }
            }
        } elseif ('PUT' == $request_method || 'DELETE' == $request_method) {
            $request_params = file_get_contents('php://input');
        } else {
            $request_params = null;
        }

        // Get URL from `csurl` in GET or POST data, before falling back to X-Proxy-URL header.
        if (isset($_REQUEST['csurl'])) {
            $request_url = urldecode($_REQUEST['csurl']);
        } elseif (isset($_SERVER['HTTP_X_PROXY_URL'])) {
            $request_url = urldecode($_SERVER['HTTP_X_PROXY_URL']);
        } else {
            $result = $this->resultJsonFactory->create();
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND);
            return $result;
        }

        $p_request_url = parse_url($request_url);

        // csurl may exist in GET request methods
        if (is_array($request_params) && array_key_exists('csurl', $request_params)) {
            unset($request_params['csurl']);
        }

        // ignore requests for proxy :)
        if (preg_match('!' . $_SERVER['SCRIPT_NAME'] . '!', $request_url) || empty($request_url) || count($p_request_url) == 1) {
            return $this->csajax_debug_message('Invalid request - make sure that csurl variable is not empty');
        }

        // check against valid requests
        if (self::CSAJAX_FILTERS) {
            $parsed = $p_request_url;
            if (self::CSAJAX_FILTER_DOMAIN) {
                if (!in_array($parsed['host'], $valid_requests)) {
                    return $this->csajax_debug_message('Invalid domain - ' . $parsed['host'] . ' does not included in valid requests');
                }
            } else {
                $check_url = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
                $check_url .= isset($parsed['user']) ? $parsed['user'] . ($parsed['pass'] ? ':' . $parsed['pass'] : '') . '@' : '';
                $check_url .= isset($parsed['host']) ? $parsed['host'] : '';
                $check_url .= isset($parsed['port']) ? ':' . $parsed['port'] : '';
                $check_url .= isset($parsed['path']) ? $parsed['path'] : '';
                if (!in_array($check_url, $valid_requests)) {
                    return $this->csajax_debug_message('Invalid domain - ' . $request_url . ' does not included in valid requests');
                }
            }
        }

        // append query string for GET requests
        if ($request_method == 'GET' && count($request_params) > 0 && (!array_key_exists('query', $p_request_url) || empty($p_request_url['query']))) {
            $request_url .= '?' . http_build_query($request_params);
        }

        // let the request begin
        $ch = curl_init($request_url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);   // (re-)send headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // return response
        curl_setopt($ch, CURLOPT_HEADER, true);       // enabled response headers
        // add data for POST, PUT or DELETE requests
        if ('POST' == $request_method) {
            $post_data = is_array($request_params) ? http_build_query($request_params) : $request_params;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,  $post_data);
        } elseif ('PUT' == $request_method || 'DELETE' == $request_method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
        }

        // retrieve response (headers and content)
        $response = curl_exec($ch);
        curl_close($ch);
        // split response to header and content
        list($response_headers, $response_content) = preg_split('/(\r\n){2}/', $response, 2);

        // (re-)send the headers
        $response_headers = preg_split('/(\r\n){1}/', $response_headers);
        $result = $this->resultJsonFactory->create();
        foreach ($response_headers as $key => $response_header) {

            // Rewrite the `Location` header, so clients will also use the proxy for redirects.
            if (preg_match('/^Location:/', $response_header)) {
                list($header, $value) = preg_split('/: /', $response_header, 2);
               // $response_header = 'Location: ' . $_SERVER['REQUEST_URI'] . '?csurl=' . $value;
                $response_header = $_SERVER['REQUEST_URI'] . '?csurl=' . $value;
                $result->setLocation($response_header);
            }
            if (!preg_match('/^(Transfer-Encoding):/', $response_header)) {
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            }
        }
        //$result->setData($response_content);
        print_r($response_content);
        return $result;

    }
    public function csajax_debug_message($message)
    {
        $result  = $this->resultJsonFactory->create();
        return $result->setData($message);

    }


}

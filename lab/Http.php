<?php
namespace WekiWork;

use Moorexa\HttpApi;
use GuzzleHttp\Psr7\Request;
use utility\Classes\BootMgr\Manager as BootMgr;

/**
 * @package Simple HTTP Request Handler for Moorexa Api
 * @author Amadi ifeanyi <amadiify.com> wekiwork.com
 */

class Http
{
    // url
    public static $endpoint;
    // instance
    private static $instance;
    // client
    private static $client;
    // headers
    private static $headers = [];
    // trash
    private $trash = [];
    // files
    private $attachment = ['multipart'=>[],'query'=>[]];
    // ready state
    private static $readyState = [];
    // using same origin
    private $usingSameOrigin = false;
    // same origin url
    private $sameOriginUrl = null;
    // same origin data
    public $sameOriginData = [];
    // same origin response
    private $sameOrginResponse = null;

    // create instance
    public static function createInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self; // create instance
            self::$client = new \GuzzleHttp\Client(); // set client
            self::setHeader(self::autoHeaders()); // set auto headers
        }
    }
    
    // create request
    public static function __callStatic($method, $data)
    {
        // create instance
        self::createInstance();

        // switch
        return self::manageSwitch($method, $data);
    }

    // add body
    private function addBodyToRequest($data)
    {
        if (count($data) == 1 && is_string($data[0]))
        {
            self::$instance->attachment['multipart'][] = [
                'contents' => isset($_POST[$data[0]]) ? $_POST[$data[0]] : $data[0],
                'name' => $data[0]
            ];
        }
        elseif (count($data) == 1 && is_array($data[0]))
        {
            foreach ($data[0] as $key => $val)
            {
                self::$instance->attachment['multipart'][] = [
                    'name' => $key,
                    'contents' => $val
                ];
            }
        }
        elseif (count($data) > 1)
        {
            foreach ($data as $index => $key)
            {
                if (isset($_POST[$key]))
                {
                    self::$instance->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => $_POST[$key]
                    ];
                }
            }
        }
        else
        {
            if (count($_POST) > 0)
            {
                $post = new \Moorexa\HttpPost();
                $data = $post->data();

                foreach ($data as $key => $val)
                {
                    self::$instance->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => $val
                    ];
                }
            }
        }
    }

    // add params
    private function addQueryToRequest($data)
    {
        if (count($data) == 1 && is_string($data[0]))
        {
            self::$instance->attachment['query'] = isset($_GET[$data[0]]) ? $_GET[$data[0]] : $data[0];
        }
        elseif (count($data) == 1 && is_array($data[0]))
        {
            self::$instance->attachment['query'] = http_build_query($data[0]);
        }
        elseif (count($data) > 1)
        {
            $get = [];

            foreach ($data as $index => $key)
            {
                if (isset($_GET[$key]))
                {
                    $get[$key] = $_GET[$key];
                }
            }

            if (count($get) > 0)
            {
                self::$instance->attachment['query'] = http_build_query($get);
            }
        }
        else
        {
            self::$instance->attachment['query'] = http_build_query($_GET);
        }
    }

    // add file
    private function addFileToRequest($data)
    {
        self::setHeader([
            'X-File-Agent' => 'Moorexa GuzzleHttp'
        ]);
        // attach file
        call_user_func_array([self::$instance, 'attachFile'], $data);
    }

    // manage switch
    private static function manageSwitch($method, $data)
    {
        // get method
        switch (strtolower($method))
        {
            case 'attach':
            case 'attachment':
                self::$instance->addFileToRequest($data);
            break;

            case 'body':
                self::$instance->addBodyToRequest($data);
            break;

            case 'query':
                self::$instance->addQueryToRequest($data);
            break;

            case 'multipart':
                self::$instance->addFileToRequest($data);
                self::$instance->addBodyToRequest($data);
                self::$instance->addQueryToRequest($data);
            break;

            case 'header':
                // set header
                call_user_func_array('\WekiWork\Http::setHeader', $data);
            break;

            default:
                return self::$instance->sendRequest($method, $data[0]);
        }

        // return instance
        return self::$instance;
    }

    // attach a file
    public function attachFile()
    {
        $files = func_get_args();

        if (count($files) == 0 && count($_FILES) > 0)
        {
            $files = array_keys($_FILES);
        }

        // check if file exists.
        array_walk($files, function($file, $key){
            if (is_string($file))
            {
                $key = 'file';

                if (file_exists($file))
                {
                    // create resource
                    $handle = fopen($file, 'r');
                    // get base 
                    $base = basename($file);
                    $key = substr($base, 0, strpos($base,'.'));

                    // add to attachment
                    $this->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => $handle,
                        'filename' => $base
                    ];
                }
                else
                {
                    // upload file
                    $uploadFile = function($file, $files, $filename='upload')
                    {
                        // create dir
                        $tmpdir = PATH_TO_STORAGE . 'Tmp/';

                        $key = $file;
                        // create destination and upload to a tmp directory
                        $name = $files['name'];
                        $tmpdir .= $name;
                        // move file
                        if (move_uploaded_file($files['tmp_name'], $tmpdir))
                        {
                            // get handle
                            $handle = fopen($tmpdir, 'r');

                            // attach file
                            $this->attachment['multipart'][] = [
                                'name' => $filename,
                                'contents' => $handle,
                                'filename' => $name
                            ];

                            // add to trash
                            $this->trash[] = $tmpdir;
                        }
                    };

                    if (isset($_FILES[$file]))
                    {
                        $files = $_FILES[$file];

                        if (!is_array($files['name']))
                        {
                            // upload file.
                            $uploadFile($file, $files, $file);
                        }
                        else
                        {
                            foreach ($files['name'] as $index => $name)
                            {
                                $build = ['name' => $name, 'tmp_name' => $files['tmp_name'][$index]];

                                // upload file
                                $uploadFile($file,$build,$file);
                            }
                        }
                    }
                }
                
            }
            elseif (is_array($file))
            {
                if ($this->usingSameOrigin === false)
                {
                    // upload file
                    $uploadFile = function($file, $files, $filename='upload')
                    {
                        // create dir
                        $tmpdir = PATH_TO_STORAGE . 'Tmp/';

                        $key = $file;
                        // create destination and upload to a tmp directory
                        $name = $files['name'];
                        $tmpdir .= $name;
                        // move file
                        if (move_uploaded_file($files['tmp_name'], $tmpdir))
                        {
                            // get handle
                            $handle = fopen($tmpdir, 'r');

                            // attach file
                            $this->attachment['multipart'][] = [
                                'name' => $filename,
                                'contents' => $handle,
                                'filename' => $key
                            ];

                            // add to trash
                            $this->trash[] = $tmpdir;
                        }
                    };

                    foreach ($file as $key => $v)
                    {
                        if (is_array($file[$key]['name']))
                        {
                            foreach ($file[$key]['name'] as $i => $name)
                            {
                                $files = ['name' => $name, 'tmp_name' => $file[$key]['tmp_name'][$i]];
                                $uploadFile($name, $files, $key);
                            }
                        }
                        elseif (is_string($file[$key]['name']))
                        {
                            $files = ['name' => $file[$key]['name'], 'tmp_name' => $file[$key]['tmp_name']];
                            $uploadFile($file[$key]['name'], $files, $key);
                        }
                    }
                }
            }
        });
    }

    // caller method
    public function __call($method, $data)
    {
        if (is_null(self::$instance))
        {
            self::$instance = $this;
        }

        // switch
        return self::manageSwitch($method, $data);
    }

    // check ready state
    public static function onReadyStateChange($callback)
    {
        self::$readyState[] = $callback;
    }

    // on state change
    public function stateChanged($code, $response=null)
    {
        $readyState = self::$readyState;

        if (count($readyState) > 0)
        {
            $data = [$code, $response, (is_object($response) ? $response->json : null)];

            foreach ($readyState as $i => $callback)
            {
                if (is_callable($callback))
                {
                    \Moorexa\Route::getParameters($callback, $const, $data);
                    call_user_func_array($callback, $const);
                }
            }
        }
    }

    // headers
    private function sendRequest($method, $path)
    {
        // inspect path
        $inspect = parse_url($path);

        // endpoint
        $endpoint = self::$endpoint;

        if ($this->sameOriginUrl === null)
        {
            if ($endpoint == '/')
            {
                if (!isset($inspect['scheme']))
                {
                    $path = url($path);
                }
            }
            else
            {
                if (!isset($inspect['scheme']))
                {
                    $path = rtrim($endpoint, '/') . '/' . $path;
                }
            }
        }
        else
        {
            $path = $this->sameOriginUrl . '/' . $path;
        }

        $client = self::$client;
        $headers = self::$headers;

        // cookie jar
        $jar = new \GuzzleHttp\Cookie\CookieJar();

        // add request body
        $requestBody = [
            'headers' => $headers,
            'debug' => false,
            'jar' => $jar
        ];

        // merge 
        $requestBody = array_merge($requestBody, $this->attachment);

        // reset
        $this->attachment = ['multipart'=>[],'query'=>[]];

        if ($this->usingSameOrigin === false)
        {
            // send request
            $send = $client->request($method, $path, $requestBody);

            // response
            $response = new class ($send)
            {
                public $guzzle; // guzzle response
                public $status; // response status
                public $statusText; // response status
                public $responseHeaders; // response headers
                public $text; // response body text
                public $json; // response body json

                // constructor
                public function __construct($response)
                {
                    $this->guzzle = $response;
                    $this->status = $response->getStatusCode();
                    $this->responseHeaders = $response->getHeaders(); 
                    $this->statusText = $response->getReasonPhrase();

                    // get body
                    $body = $response->getBody()->getContents();
                    $this->text = $body;

                    // get json 
                    $json = is_string($body) ? json_decode($body) : null;
                    if (!is_null($json) && is_object($json))
                    {
                        $this->json = $json;
                    }
                }
            };
        }
        else
        {
            $response = $this->handleRequestBySameOrigin($method, $path, $requestBody);
        }

        // state changed
        $this->stateChanged(4, $response);

        return $response;
    }

    // get all auto headers
    public static function autoHeaders()
    {
        $headers = [];

        // get default headers
        if (file_exists(HOME . 'api/config.xml'))
        {
            $config = simplexml_load_file(HOME . 'api/config.xml');

            if ($config !== false)
            {
                $arr = toArray($config);

                if (isset($arr['request']))
                {
                    if (isset($arr['request']['identifier']))
                    {
                        $arr = $arr['request']['identifier'];
                        
                        if (is_array($arr) && isset($arr[0]))
                        {
                            array_map(function($a) use (&$headers){
                                if (isset($a['header']))
                                {
                                    $header = trim(strtolower($a['header']));
                                    $valStored = trim($a['value']);

                                    $headers[$header] = $valStored;
                                }
                            }, $arr);
                        }
                        else
                        {
                            $headers[$arr['header']] = $arr['value'];
                        }
                    }
                }
            }
        }

        return $headers;
    }

    // set header
    public static function setHeader($header)
    {
        $current = self::$headers;

        if (is_array($header))
        {
            $current = array_merge($current, $header);
            self::$headers = $current;
        }
        else
        {
            $args = func_get_args();
            $headers = [];

            foreach ($args as $index => $header)
            {
                $toArray = explode(':', $header);
                $key = trim($toArray[0]);
                $val = trim($toArray[1]);
                $headers[$key] = $val;
            }

            $current = array_merge($current, $headers);
            self::$headers = $current;
        }

        // clean up
        $current = null;
    }

    // get all headers
    public static function getHeaders()
    {
        $headers = getallheaders();
        $newHeader = [];

        $headers = array_merge($headers, self::$headers);

        foreach ($headers as $header => $value)
        {
            $newHeader[strtolower($header)] = $value;
        }   

        return $newHeader;
    }

    // has header
    public static function hasHeader(string $header, &$value=null) :bool
    {
        $headers = self::getHeaders();

        if (isset($headers[strtolower($header)]))
        {
            $value = $headers[strtolower($header)];

            return true;
        }

        return false;
    }

    // create same origin
    public static function sameOrigin($callback = null)
    {
        // create object
        $http = new Http;
        $http->sameOriginUrl = false; // app url
        $http->usingSameOrigin = true;

        $sameOrginResponse = function(&$http)
        {
            return new class($http){
                public $status = 0;
                public $json = null;
                public $text = null;
    
                public function __construct($http)
                {
                    $sameOriginData = $http->sameOriginData;
    
                    if (isset($sameOriginData['status']))
                    {
                        // set status
                        $this->status = $sameOriginData['status'];
                        // set text response
                        $this->text = $sameOriginData['text'];
                        // set json
                        $this->json = $sameOriginData['json'];
                    }
                }
            };
        };

        if (is_callable($callback) && !is_null($callback))
        {
            // call closure function
            call_user_func_array($callback, [&$http]);

            return call_user_func_array($sameOrginResponse, [&$http]);
        }
        else
        {
            $http->sameOrginResponse = $sameOrginResponse;
        }

        return $http;
    }

    // handle request by same origin
    private function handleRequestBySameOrigin(string $method, string $path, array $requestBody)
    {
        // save previous request data
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $get = $_GET;
        $uri = isset($_SERVER['REQUEST_QUERY_STRING']) ? $_SERVER['REQUEST_QUERY_STRING'] : null;
        $post = $_POST;
        $file = $_FILES;

        // set new method
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);

        // set new paths
        $path = ltrim($path, '/');
        $_GET['__app_request__'] = $path;
        $_SERVER['REQUEST_QUERY_STRING'] = $path;

        // set new headers
        if (isset($requestBody['headers']))
        {
            $headers = $requestBody['headers'];
            foreach ($headers as $key => $value)
            {
                self::$headers[$key] = $value;
            }
        }

        // add new post body
        if (isset($requestBody['multipart']))
        {
            $multipart = $requestBody['multipart'];
            foreach ($multipart as $body)
            {
                $name = $body['name'];
                $contents = $body['contents'];

                if (isset($body['filename']))
                {
                    $filename = $body['filename'];
                    
                    $_FILES[$name] = [
                        'name' => $filename,
                        'error' => 0
                    ];
                }
                else
                {
                    $_POST[$name] = $contents;
                }
            }
        }

        // add new query to $_GET
        if (isset($requestBody['query']) && is_string($requestBody['query']))
        {
            $query = parse_query($requestBody['query']);
            $_GET = array_merge($_GET, $query);
        }

        // create buffer here
        ob_start(); 
        if (!\Moorexa\Engine::$instance->assistRequest())
        {
            \Moorexa\Middleware::System()->eventCallback('ready');   
        }
        $out = ob_get_contents();
        ob_clean();
        // end here


        // save response and status code
        $this->sameOriginData = [
            'status' => http_response_code(),
            'text' => trim($out),
            'json' => json_decode(trim($out))
        ];
        
        //### Reset block here
        // remove headers
        if (isset($requestBody['headers']))
        {
            $headers = $requestBody['headers'];
            foreach ($headers as $key => $value)
            {
                unset(self::$headers[$key]);
            }
        }

        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $_GET = $get;
        $_POST = $post;
        $_FILES = $file;
        $_SERVER['REQUEST_QUERY_STRING'] = $uri;

        // reset content-type
        header('Content-Type: text/html', true);

        // reset render
        \Moorexa\Controller::$rendering = false;

        if ($this->sameOrginResponse !== null)
        {
            return call_user_func_array($this->sameOrginResponse, [&$this]);
        }
    }
}
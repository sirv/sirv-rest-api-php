<?php
/**
 * Sirv REST API PHP class
 *
 * This file helps you connect to the Sirv API and run its API methods.
 * 
 * How to connect: https://sirv.com/help/articles/sirv-rest-api/
 * List of API methods: https://api.sirv.com/v2/docs
 *
 * @author    Sirv Limited <support@sirv.com>
 * @license   https://www.sirv.com/
 */

class SirvAPIClient
{
    private $clientId = '';
    private $clientSecret = '';
    private $token = '';
    private $tokenExpireTime = 0;
    private $connected = false;
    private $lastResponse;
    private $userAgent;
    private $mutedTill = 0;

    public function __construct(
        $clientId,
        $clientSecret,
        $token = '',
        $tokenExpireTime = '',
        $userAgent = 'Sirv REST API PHP client'
    ) {

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->token = $token;
        $this->tokenExpireTime = $tokenExpireTime;
        $this->userAgent = $userAgent;
        $this->preOperationCheck();
    }


    // Upload file
    // https://api.sirv.com/v2/docs?path=/v2/files/upload#POST
    public function uploadFile($fs_path, $sirv_path)
    {
        $preCheck = $this->preOperationCheck();
        if (!$preCheck) {
            return false;
        }

        $path_info = pathinfo($sirv_path);
        $path_info['dirname'] = $path_info['dirname'] == '.' ? '' : '/' . $path_info['dirname'];
        $encoded_sirv_path = $path_info['dirname'] . '/' . rawurlencode($path_info['basename']);
        $encoded_sirv_path = $this->clean_symbols($encoded_sirv_path);

        $content_type = '';
        if (function_exists('mime_content_type')) {
            $content_type = mime_content_type($fs_path) !== false ? mime_content_type($fs_path) : 'application/octet-stream';
        } else {
            $content_type = "image/" . $path_info['extension'];
        }

        $headers = array(
            'Content-Type'   => $content_type,
            'Content-Length' => filesize($fs_path),
        );

        $res = $this->sendRequest(
            'v2/files/upload?filename=' . $encoded_sirv_path,
            file_get_contents($fs_path), 
             'POST', 
             '', 
             $headers,
            true
        );
        
        return ($res && $res->http_code == 200);
    }   

    public function getToken() {
        return $this->token;
    }

    public function getTokenExpireTime() {
        return $this->tokenExpireTime;
    }

    // Fetch URL
    // https://api.sirv.com/v2/docs?path=/v2/files/fetch#POST
    public function fetchImage($imgs)
    {
        $preCheck = $this->preOperationCheck();
        if (!$preCheck) {
            return false;
        }

        $res = $this->sendRequest(
            'v2/files/fetch',
            $imgs,
            'POST'
        );

        print_r($res);

        if ($res) {
            $this->connected = true;
            return $res;
        } else {
            $this->connected = false;
            $this->nullToken();
            return false;
        }
    }

    public function preOperationCheck()
    {

        if (empty($this->token) || $this->isTokenExpired()) {
            $this->getNewToken();
        }

        if ($this->connected) {
            return true;
        }

        if (empty($this->token) || $this->isTokenExpired()) {
            if (!$this->getNewToken()) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function getNewToken()
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            $this->nullClientLogin();
            $this->nullToken();
            return false;
        }
        $res = $this->sendRequest('v2/token', array(
            "clientId" => $this->clientId,
            "clientSecret" => $this->clientSecret,
        ));

        if ($res && $res->http_code == 200 && !empty($res->result->token) && !empty($res->result->expiresIn)) {
            $this->connected = true;
            $this->token = $res->result->token;
            $this->tokenExpireTime = time() + $res->result->expiresIn;
            return $this->token;
        } else {
            $this->connected = false;
            if (!empty($res->http_code) && $res->http_code == 401) {
                $this->nullClientLogin();
            }
            $this->nullToken();
            return false;
        }
    }

    private static function usersSortFunc($a, $b)
    {
        if ($a->alias == $b->alias) {
            return 0;
        }
        return ($a->alias < $b->alias) ? -1 : 1;
    }

    // Get folder options
    // https://api.sirv.com/v2/docs?path=/v2/files/options#GET
    public function getFolderOptions($filename)
    {
        $res = $this->sendRequest(
            'v2/files/options?filename=/'.rawurlencode($filename).'&withInherited=true',
            array(),
            'GET'
        );
        if ($res && $res->http_code == 200) {
            return $res->result;
        } else {
            return false;
        }
    }

    // Set folder options
    // https://api.sirv.com/v2/docs?path=/v2/files/options#POST
    public function setFolderOptions($filename, $options)
    {
        $res = $this->sendRequest(
            'v2/files/options?filename=/'.rawurlencode($filename),
            $options,
            'POST'
        );
        print_r($res); die();
        return ($res && $res->http_code == 200);
    }

    // Read folder contents
    // https://api.sirv.com/v2/docs?path=/v2/files/readdir#GET
    public function getFolderContents($filename)
    {
        $contents = array(); $continuation = false;
        do {            
            $res = $this->sendRequest(
                'v2/files/readdir?&dirname=/'.rawurlencode($filename). ( (!empty($continuation))?'&continuation='.rawurlencode($continuation):''  ),
                array(),
                'GET'
            );
            if (!($res && $res->http_code == 200)) {
                return !empty($contents)?$contents:false;
            }
            $contents = array_merge($contents, array_values($res->result->contents));
            $continuation = (isset($res->result->continuation) && !empty($res->result->continuation))?$res->result->continuation:'';
            
        } while (!empty($continuation));

        return $contents;
    }    

    // Delete file or empty directory
    // https://api.sirv.com/v2/docs?path=/v2/files/delete#POST
    public function deleteFile($filename)
    {
        $res = $this->sendRequest(
            'v2/files/delete?filename=/'.rawurlencode($filename),
            array(),
            'POST'
        );

        return ($res && $res->http_code == 200);
    }    

    private function clean_symbols($str)
    {
        $str = str_replace('%40', '@', $str);
        $str = str_replace('%5D', '[', $str);
        $str = str_replace('%5B', ']', $str);
        $str = str_replace('%7B', '{', $str);
        $str = str_replace('%7D', '}', $str);
        $str = str_replace('%2A', '*', $str);
        $str = str_replace('%3E', '>', $str);
        $str = str_replace('%3C', '<', $str);
        $str = str_replace('%24', '$', $str);
        $str = str_replace('%3D', '=', $str);
        $str = str_replace('%2B', '+', $str);
        $str = str_replace('%28', '(', $str);
        $str = str_replace('%29', ')', $str);

        return $str;
    }
 
    
    // Get file info
    // https://api.sirv.com/v2/docs?path=/v2/files/stat#GET
    public function getFileStat($filename)
    {
        $res = $this->sendRequest(
            'v2/files/stat?filename=/'.rawurlencode($filename),
            array(),
            'GET'
        );

        if ($res && $res->http_code == 200) {
            return $res->result;
        } else {
            return false;
        }
    }

    // Get profiles list
    public function getProfiles()
    {

        $res = $this->sendRequest(
            'v2/files/readdir?dirname=/Profiles',
            array(),
            'GET'
        );

        if ($res && $res->http_code == 200) {
            return $res->result;
        } else {
            return false;
        }
    }
    
    public function nullClientLogin()
    {
        $this->clientId = '';
        $this->clientSecret = '';
    }

    public function nullToken()
    {
        $this->token = '';
        $this->tokenExpireTime = 0;
    }

    public function isTokenExpired()
    {
        return $this->tokenExpireTime < time();
    }

    // Get account info
    // https://api.sirv.com/v2/docs?path=/v2/account#GET
    public function getAccountInfo()
    {
        $preCheck = $this->preOperationCheck();
        if (!$preCheck) {
            return false;
        }

        $result = $this->sendRequest('v2/account', array(), 'GET');

        if (!$result || empty($result->result) || $result->http_code != 200 || empty($result->result)) {
            $this->connected = false;
            $this->nullToken();
            return false;
        }

        return $result->result;
    }

    private function getFormatedFileSize($bytes, $fileName = "", $decimal = 2, $bytesInMM = 1000)
    {
        if (!empty($fileName)) {
            $bytes = filesize($fileName);
        }

        $sign = ($bytes>=0)?'':'-';
        $bytes = abs($bytes);

        if (is_numeric($bytes)) {
            $position = 0;
            $units = array( " Bytes", " KB", " MB", " GB", " TB" );
            while ($bytes >= $bytesInMM && ($bytes / $bytesInMM) >= 1) {
                 $bytes /= $bytesInMM;
                 $position++;
            }
            return ($bytes==0)?'-':$sign.round($bytes, $decimal).$units[$position];
        } else {
            return "-";
        }
    }


    // Get storage, plan and traffic info
    public function getStorageInfo()
    {
        $preCheck = $this->preOperationCheck();
        if (!$preCheck) {
            return false;
        }

        $storageInfo = array();

        $result = $this->sendRequest('v2/account', array(), 'GET');
        $result_storage = $this->sendRequest('v2/account/storage', array(), 'GET');

        if (!$result || empty($result->result) || $result->http_code != 200
            || !$result_storage->result || empty($result->result) || $result_storage->http_code != 200) {
            $this->connected = false;
            $this->nullToken();
            return false;
        }

        $result = $result->result;
        $result_storage = $result_storage->result;

        if (isset($result->alias)) {
            $storageInfo['account'] = $result->alias;

            $billing = $this->sendRequest('v2/billing/plan', array(), 'GET');

            $billing->result->dateActive = preg_replace(
                '/.*([0-9]{4}\-[0-9]{2}\-[0-9]{2}).*/ims',
                '$1',
                $billing->result->dateActive
            );

            $planEnd = strtotime('+30 days', strtotime($billing->result->dateActive));
            $now = time();

            $datediff = (int) round(($planEnd - $now) / (60 * 60 * 24));

            $until = ($planEnd > $now) ? ' (' . $datediff . ' day' . ($datediff > 1 ? 's' : '') . ' left)' : '';

            if ($planEnd < $now) {
                $until = '';
            }

            $storageInfo['plan'] = array(
                'name' => $billing->result->name,
                'trial_ends' => preg_match('/trial/ims', $billing->result->name) ?
                    'until ' . date("j F", strtotime('+30 days', strtotime($billing->result->dateActive))) . $until
                    : '',
                'storage' => $billing->result->storage,
                'storage_text' => $this->getFormatedFileSize($billing->result->storage),
                'dataTransferLimit' => isset($billing->result->dataTransferLimit) ?
                    $billing->result->dataTransferLimit : '',
                'dataTransferLimit_text' => isset($billing->result->dataTransferLimit) ?
                    $this->getFormatedFileSize($billing->result->dataTransferLimit) : '&#8734',
            );

            $storage = $this->sendRequest('v2/account/storage', array(), 'GET');

            $storage->result->plan = $storage->result->plan + $storage->result->extra;

            $storageInfo['storage'] = array(
                'allowance' => $storage->result->plan,
                'allowance_text' => $this->getFormatedFileSize($storage->result->plan),
                'used' => $storage->result->used,
                'available' => $storage->result->plan - $storage->result->used,
                'available_text' => $this->getFormatedFileSize($storage->result->plan - $storage->result->used),
                'available_percent' => number_format(
                    ($storage->result->plan - $storage->result->used) / $storage->result->plan * 100,
                    2,
                    '.',
                    ''
                ),
                'used_text' => $this->getFormatedFileSize($storage->result->used),
                'used_percent' => number_format($storage->result->used / $storage->result->plan * 100, 2, '.', ''),
                'files' => $storage->result->files,
            );

            $storageInfo['traffic'] = array(
                'allowance' => isset($billing->result->dataTransferLimit) ? $billing->result->dataTransferLimit : '',
                'allowance_text' => isset($billing->result->dataTransferLimit) ?
                $this->getFormatedFileSize($billing->result->dataTransferLimit) : '&#8734',
            );

            $dates = array(
                'This month' => array(
                    date("Y-m-01"),
                    date("Y-m-t"),
                ),
                date("F Y", strtotime("first day of -1 month")) => array(
                    date("Y-m-01", strtotime("first day of -1 month")),
                    date("Y-m-t", strtotime("last day of -1 month")),
                ),
                date("F Y", strtotime("first day of -2 month")) => array(
                    date("Y-m-01", strtotime("first day of -2 month")),
                    date("Y-m-t", strtotime("last day of -2 month")),
                ),
                date("F Y", strtotime("first day of -3 month")) => array(
                    date("Y-m-01", strtotime("first day of -3 month")),
                    date("Y-m-t", strtotime("last day of -3 month")),
                ),
            );

            $dataTransferLimit = isset($billing->result->dataTransferLimit) ?
            $billing->result->dataTransferLimit : PHP_INT_MAX;

            foreach ($dates as $label => $date) {
                $traffic = $this->sendRequest('v2/stats/http?from=' . $date[0] . '&to=' . $date[1], array(), 'GET');

                if (!$traffic || $traffic->http_code != 200) {
                    $this->connected = false;
                    $this->nullToken();
                    return false;
                }

                unset($traffic->http_code);

                $traffic = (array)$traffic->result;

                $storageInfo['traffic']['traffic'][$label]['size'] = 0;

                if (count($traffic)) {
                    foreach ($traffic as $v) {
                        $storageInfo['traffic']['traffic'][$label]['size'] += (isset($v->total->size))
                        ? $v->total->size : 0;
                    }
                }
                $storageInfo['traffic']['traffic'][$label]['percent'] = number_format(
                    $storageInfo['traffic']['traffic'][$label]['size'] / $dataTransferLimit * 100,
                    2,
                    '.',
                    ''
                );
                $storageInfo['traffic']['traffic'][$label]['percent_reverse'] = number_format(
                    100 - $storageInfo['traffic']['traffic'][$label]['size'] / $dataTransferLimit * 100,
                    2,
                    '.',
                    ''
                );
                $storageInfo['traffic']['traffic'][$label]['size_text'] =
                $this->getFormatedFileSize($storageInfo['traffic']['traffic'][$label]['size']);
            }
        }

        $result = $this->sendRequest('v2/account/limits', array(), 'GET');

        if ($result && !empty($result->result) && $result->http_code == 200) {
            $storageInfo['limits'] = $result->result;
            $storageInfo['limits'] = (array)$storageInfo['limits'];
            $date = new \DateTime();
            $timeZone = $date->getTimezone();
            foreach ($storageInfo['limits'] as $type => $value) {
                $storageInfo['limits'][$type] = (array)$value;
                $value = (array)$value;
                $dt = new \DateTime('@'.$value['reset']);
                $dt->setTimeZone(new \DateTimeZone($timeZone->getName()));
                $storageInfo['limits'][$type]['reset_str'] = $dt->format("H:i:s");
                $storageInfo['limits'][$type]['used'] = ($value['limit']==0)?'100%':
                (round($value['count']/$value['limit']*10000)/100).'%';
                $storageInfo['limits'][$type]['type'] = $type;
            }
            $storageInfo['limits'] = array_chunk($storageInfo['limits'], (int)count($storageInfo['limits'])/2);
        }

        return $storageInfo;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    private function muteRequests($timestamp)
    {
        // pause requests till $timestamp
        $this->mutedTill = $timestamp;
    }

    public function isMuted()
    {
        return ((int)$this->mutedTill > time());
    }

    private function sendRequest($url, $data, $method = 'POST', $token = '', $headers = null, $isFile = false)
    {

        if ($this->isMuted()) {
            $this->curlInfo = array('http_code' => 429);
            return false;
        }

        $curl = curl_init();
        
        if (is_null($headers)) {
            $headers = array();
        }


        if (!empty($token)) {
            $headers['Authorization'] = "Bearer " . ((!empty($token)) ? $token : $this->token);
        } else {
            $headers['Authorization'] = "Bearer " . $this->token;
        }
        if(!array_key_exists('Content-Type', $headers)) $headers['Content-Type'] = "application/json";

        foreach ($headers as $k => $v){
            $headers[$k] = "$k: $v";
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sirv.com/" . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,

            CURLOPT_CONNECTTIMEOUT => 0.1,
            CURLOPT_TIMEOUT => 0.1,

            CURLOPT_USERAGENT => $this->userAgent,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => (!$isFile) ? json_encode($data) : $data,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $result = curl_exec($curl);

        $info = curl_getinfo($curl);

        if ($info['http_code'] == 429 &&
            preg_match('/Retry after ([0-9]{4}\-[0-9]{2}\-[0-9]{2}.*?\([a-z]{1,}\))/ims', $result, $m)) {
            $time = strtotime($m[1]);
            $this->muteRequests($time);
        }

        $response = (object) $info;
        $response->result = json_decode($result);
        $response->error = curl_error($curl);

        $this->lastResponse = $response;

        curl_close($curl);

        return $response;
    }
}

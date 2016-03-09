<?php

namespace Zan\Framework\Sdk\Qiniu;


class Qiniu
{

    private static $config;

    public static function setConfig(array &$config){
        self::$config = &$config;
    }

    /**
     * get the qiniu url
     * @param  [string] $url [qiniu key]
     * @param  [string] $fop [process imGage]
     * @return [string]
     */
    public static function site($url_string = '', $fop = '')
    {
        if (strpos($url_string, 'koudaitong') !== FALSE
            || strpos($url_string, 'kdt') !== FALSE
            || strpos($url_string, 'yzcdn') !== FALSE
            || strpos($url_string, 'upload_files') !== FALSE
            || strpos($url_string, 'public_files') !== FALSE
        ) {
            $bucket = self::getBucketByUrl($url_string);
            // process fop
            foreach (self::$config['bucket'][$bucket]['fop'] as $key => $value) {
                if (strpos($url_string, $value) !== FALSE) {
                    $fop = $key;
                    $url_string = str_replace($value, '', $url_string);
                    break;
                }
            }

            $url_array = parse_url($url_string);
            $fop = self::getFop($bucket, $fop);
            if (self::isPublicBucket($bucket)) {
                return self::getPublicUrl($bucket, trim($url_array['path'], '/'), $fop);
            }

            return self::getPrivateUrl($bucket, trim($url_array['path'], '/'), $fop);
        }

        return self::$config['no_pic_url'];
    }

//    /**
//     * get qiniu upload token
//     * @param  [array] $policy  [mp_id Scope array]
//     * @return [string]         [token string]
//     */
//    public static function getUpToken($policy=[], $need_callback=TRUE)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['qiniu.secret_key'];
//        Qiniu_SetKeys($accessKey, $secretKey);
//
//        $putPolicy = new Qiniu_RS_PutPolicy($policy['Scope']);
//        foreach ($policy as $key => $value) {
//            isset($putPolicy->$key) AND $putPolicy->$key = $value;
//        }
//        if ($need_callback === TRUE) {
//            $putPolicy->CallbackBody = self::getCallBackBody($policy['mp_id']);
//            $putPolicy->CallbackUrl = self::getCallbackUrl();
//            $putPolicy->SaveKey = self::getSaveKey($policy['Scope']);
//        }
//        $upToken = $putPolicy->Token(null);
//
//        return $upToken;
//    }

    /**
     * get qiniu public url
     * @param  [string] $bucket [bucket name]
     * @param  [type] $key    	[key name]
     * @param  string $fop    	[fop name such as thumbnails]
     * @return [string]         [public url]
     */
    public static function getPublicUrl($bucket, $key, $fop = '')
    {
        $publicUrl = Qiniu_RS_MakeBaseUrl(self::getDomain($bucket), $key) ;

        return urldecode($publicUrl) . "{$fop}";
    }

    /**
     * get qiniu private url
     * @param  [string] $bucket [bucket name]
     * @param  [type] $key    	[key name]
     * @param  string $fop    	[fop name such as thumbnails]
     * @return [string]         [private url]
     */
    public static function getPrivateUrl($bucket, $key, $fop ='')
    {
        $accessKey = self::$config['access_key'];
        $secretKey = self::$config['secret_key'];

        Qiniu_SetKeys($accessKey, $secretKey);
        $baseUrl = Qiniu_RS_MakeBaseUrl(self::getDomain($bucket), $key) . $fop;
        $baseUrl = str_replace("http", "https", $baseUrl);
        $getPolicy = new Qiniu_RS_GetPolicy();
        $getPolicy->Expires = self::$config['bucket'];[$bucket]['expires'];
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null);

        //wait qiniu to support urlencode
        // return urldecode($privateUrl) . "{$fop}";
        //return $privateUrl . "{$fop}";
        return $privateUrl;
    }

    /**
     * get the qiniu Fop
     * @param  [string] $bucket [qiniu bucket]
     * @param  [string] $fop    [alias name]
     * @return [string]         [fop query string]
     */
    public static function getFop($bucket, $fop)
    {
        if (isset(self::$config['bucket'][$bucket]['fop'][$fop])) {
            return self::$config['bucket'][$bucket]['fop'][$fop];
        }

        return '';
    }

    /**
     * according bucket get the domain
     * @param  [string] $bucket [bucket name]
     * @return [string]
     */
    public static function getDomain($bucket)
    {
        if (isset(self::$config['bucket'][$bucket]['domain'])) {
            return self::$config['bucket'][$bucket]['domain'];
        }

        return current(self::$config['bucket'])['domain'];
    }

    /**
     * get bucket by given url
     * @param  [string] $url [file url]
     * @return [string]      [bucket name]
     */
    public static function getBucketByUrl($url)
    {
        $all_bucket = self::$config['bucket'];
        $first_bucket = current($all_bucket);

        foreach ($all_bucket as $bucket) {
            if (strpos($url, $bucket['name']) !== FALSE) {
                //find the bucket
                return $bucket['name'];
            }
        }

        //The first element is return by default
        return $first_bucket['name'];
    }

//    public static function getPublicBucketName()
//    {
//        $all_bucket = self::$config['bucket'];
//        $first_bucket = current($all_bucket);
//
//        return $first_bucket['name'];
//    }

    /**
     * whether this bucket is public
     * @param  [string]  $bucket [bucket name]
     * @return [bool]
     */
    public static function isPublicBucket($bucket)
    {
        if (isset(self::$config['bucket'][$bucket]['public'])) {
            return self::$config['bucket'][$bucket]['public'];
        }

        return TRUE;
    }

//    /**
//     * qiniu save key
//     * @param  [string] $bucket [bucket name]
//     * @return [string]         [save key]
//     */
//    public static function getSaveKey($bucket)
//    {
//        if (isset(self::$config['bucket'][$bucket]['save_key'])) {
//            return self::$config['bucket'][$bucket]['save_key'];
//        }
//
//        return '';
//    }
//
//    /**
//     * kdt callback url for save file info
//     * @return [url] [callback url]
//     */
//    public static function getCallbackUrl()
//    {
//        return self::$config['callback_url'];
//    }
//
//    /**
//     * callback body for callback url
//     * @param  [int] $mpId   [mp's id]
//     * @return [string]      [callback body]
//     */
//    public static function getCallBackBody($mpId)
//    {
//        return   "mp_id={$mpId}&" //Interpolation
//        .'bucket=$(bucket)&'
//        .'type=$(mimeType)&'
//        .'name=$(fname)&'
//        .'size=$(fsize)&'
//        .'key=$(key)&'
//        .'w=$(imageInfo.width)&'
//        .'h=$(imageInfo.height)&'
//        .'kdt_type=$(x:kdt_type)&'
//        .'skip_save=$(x:skip_save)&'
//        .'ext=$(ext)';
//    }
//
//    /**
//     * get image data by qiniu
//     * @param  [string] $bucket [bucket name]
//     * @param  [string] $key    [key name]
//     * @return [array]          [image data]
//     */
//    public static function getImageMetaData($bucket, $key)
//    {
//
//        $domain = self::$config['bucket'][$bucket]['domain'];
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//
//        Qiniu_SetKeys($accessKey, $secretKey);
//        //生成baseUrl
//        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
//
//        //生成fopUrl
//        $imgInfo = new Qiniu_ImageInfo;
//        $imgInfoUrl = $imgInfo->MakeRequest($baseUrl);
//
//        if (self::isPublicBucket($bucket) === FALSE) {
//            //对fopUrl 进行签名，生成privateUrl。 公有bucket 此步可以省去。
//            $getPolicy = new Qiniu_RS_GetPolicy();
//            $imgInfoUrl = $getPolicy->MakeRequest($imgInfoUrl, null);
//        }
//
//        return Json::decode(Requests::get($imgInfoUrl)->body);
//    }
//
//    /**
//     * get file meta data
//     * @param  [string] $bucket [bucket name]
//     * @param  [string] $key    [key name]
//     * @return [array]          [file data]
//     */
//    public static function getFileMetaData($bucket, $key)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//        Qiniu_setKeys($accessKey, $secretKey);
//
//        $client = new Qiniu_MacHttpClient(null);
//        list($ret, $err) = Qiniu_RS_Stat($client, $bucket, $key);
//        $ret['error'] = $err;
//        $ret['width'] = 0;
//        $ret['height'] = 0;
//
//        return $ret;
//    }
//
//    /**
//     * fetch the target url to qiniu bucket
//     * @param  [string] $targetUrl  [target url]
//     * @param  [string] $fileExt  	[file ext]
//     * @param  [string] $destBucket [bucket name]
//     * @param  [string] $destKey    [key name]
//     * @return [array]              [result]
//     */
//    public static function fetch($targetUrl, $fileExt='', $destBucket='', $destKey='')
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//
//        if (empty($destBucket)) {
//            $destBucket = current(self::$config['bucket'])['name'];
//        }
//        if (empty($destKey)) {
//            if ($fileExt) {
//                $pathName = Time::current('Y/m/d/') . md5($targetUrl) . '.' . $fileExt;
//            } else {
//                $pathName = Time::current('Y/m/d/') . md5($targetUrl) . '.' . strtolower(substr(strrchr($targetUrl, '.'), 1));
//            }
//            //Now Qiniu cannot support magic variable in interface of fetch
//            if(self::isPublicBucket($destBucket)) {
//                $destKey = 'upload_files/' . $pathName;
//            } else {
//                $destKey = "{$destBucket}/" . $pathName;
//            }
//        }
//
//        $encodedUrl = Qiniu_Encode($targetUrl);
//
//        $destEntry = "$destBucket:$destKey";
//        $encodedEntry = Qiniu_Encode($destEntry);
//
//        $apiHost = "http://iovip.qbox.me";
//        $apiPath = "/fetch/$encodedUrl/to/$encodedEntry";
//        $requestBody = "";
//
//        $mac = new Qiniu_Mac($accessKey, $secretKey);
//        $client = new Qiniu_MacHttpClient($mac);
//
//        list($ret, $err) = Qiniu_Client_CallWithForm($client, $apiHost . $apiPath, $requestBody);
//
//        if ($err !== null) {
//            return ['result' => FALSE, 'error' => $err];
//        } else {
//            //get file meta data
//            //http://developer.qiniu.com/docs/v6/api/reference/rs/stat.html
//            $metaData = self::getFileMetaData($destBucket, $destKey);
//            if (isset($metaData['error'])) {
//                return ['result' => FALSE, 'error' => URL::site($destKey)];
//            }
//
//            //never download text, if the mimeType of file is text
//            //it imply that there are wrong
//            if (strpos($metaData['mimeType'], 'text') !== FALSE) {
//                return ['result' => FALSE, 'error' => URL::site($destKey)];
//            }
//
//            //if it's an image, query width and height
//            if (strpos($metaData['mimeType'], 'image') !== FALSE) {
//                $imgData = self::getImageMetaData($destBucket, $destKey);
//                if (!isset($imgData['error'])) {
//                    $metaData['width'] = isset($imgData['width']) ? $imgData['width'] : 0;
//                    $metaData['height'] = isset($imgData['height']) ? $imgData['height'] : 0;
//                }
//            }
//
//            return array_merge(['result' => TRUE, 'error' => NULL], $metaData,
//                ['bucket' => $destBucket, 'key' => $destKey]);
//        }
//    }
//
//    /**
//     * upload local file
//     * @param  [string] $bucket   [bucket name]
//     * @param  [string] $key 	  [key name]
//     * @param  [string] $filepath [local file path (absolute path)]
//     * @return [bool]             [true or false]
//     */
//    public static function uploadFile($bucket, $key, $filepath)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//
//        Qiniu_SetKeys($accessKey, $secretKey);
//        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
//        $upToken = $putPolicy->Token(null);
//        $putExtra = new Qiniu_PutExtra();
//        $putExtra->Crc32 = 1;
//
//        list($ret, $err) = Qiniu_PutFile($upToken, $key, $filepath, $putExtra);
//
//        return $err === null ? TRUE : FALSE;
//    }
//
//    /**
//     * upload binary data
//     * @param  [string] $bucket      [description]
//     * @param  [string] $key         [description]
//     * @param  [binary data] $filecontent [description]
//     * @return [json]              [description]
//     */
//    public static function uploadBinaryFile($bucket, $key, $fileContent)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//        $boundary  = 'deadbeef';
//
//        Qiniu_SetKeys($accessKey, $secretKey);
//        $putPolicy = new Qiniu_RS_PutPolicy($bucket);
//        $upToken = $putPolicy->Token(null);
//
////It's the same as http body, So DONT change it;
//        $requestBody = <<<HTTP_BODY
//--${boundary}
//Content-Disposition:       form-data; name="token"
//
//${upToken}
//--${boundary}
//Content-Disposition:       form-data; name="key"
//
//${key}
//--${boundary}
//Content-Disposition:       form-data; name="file"; filename="${key}"
//Content-Type:              application/octet-stream
//Content-Transfer-Encoding: binary
//
//${fileContent}
//--${boundary}--
//HTTP_BODY;
//
//        //Should not be based on athoer lib
//        $ch = curl_init('up.qiniu.com');
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
//        curl_setopt($ch, CURLOPT_HTTPHEADER,[
//                "Host: up.qiniu.com",
//                "Content-Type: multipart/form-data; boundary=${boundary}",
//                "Content-Length: " . strlen($requestBody)
//            ]
//        );
//        $result = curl_exec($ch);
//
//        $json_result = json_decode($result, TRUE);
//
//        if (!$json_result) {
//            return ['error' => 1, 'msg' => $result];
//        }
//
//        if (isset($result['error'])) {
//            return ['error' => 1, 'msg' => $result['error']];
//        }
//
//        return $json_result;
//    }
//
//    /**
//     * move bucket:key to another bucket:key
//     * @param  [string] $originBucket [original bucket name]
//     * @param  [string] $originKey    [original key name]
//     * @param  [string] $destBucket   [destination bucket name]
//     * @param  [string] $destKey      [destination key name]
//     * @return [bool]                 [true or false]
//     */
//    public static function move($originBucket, $originKey, $destBucket, $destKey)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//
//        Qiniu_SetKeys($accessKey, $secretKey);
//        $client = new Qiniu_MacHttpClient(null);
//
//        $err = Qiniu_RS_Move($client, $originBucket, $originKey, $destBucket, $destKey);
//
//        return $err === null ? TRUE : FALSE;
//    }
//
//    /**
//     * delete bucket:key
//     * @param  [string] $bucket [bucket name]
//     * @param  [string] $key    [key name]
//     * @return [bool]           [true or false]
//     */
//    public static function delete($bucket, $key)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//
//        Qiniu_SetKeys($accessKey, $secretKey);
//        $client = new Qiniu_MacHttpClient(null);
//
//        $err = Qiniu_RS_Delete($client, $bucket, $key1);
//
//        return $err === null ? TRUE : FALSE;
//    }
//
//    public static function pfopWithMp3($bucket, $key)
//    {
//        $accessKey = self::$config['access_key'];
//        $secretKey = self::$config['secret_key'];
//
//        $fops = [];
//        foreach (['64k', '8k', '128k'] as $value) {
//            $bk = base64_encode("${bucket}:${key}!${value}.mp3");
//            $fops[] = "avthumb/mp3/ab/64k|saveas/${bk}";
//        }
//        $fops = implode(';', $fops);
//
//        $notifyURL = "";
//        $force = 0;
//        $encodedBucket = urlencode($bucket);
//        $encodedKey = urlencode($key);
//        $encodedFops = urlencode($fops);
//        $encodedNotifyURL = urlencode($notifyURL);
//        $apiHost = "http://api.qiniu.com";
//        $apiPath = "/pfop/";
//        $requestBody = "bucket=$encodedBucket&key=$encodedKey&fops=$encodedFops&notifyURL=$encodedNotifyURL";
//        if ($force !== 0) {
//            $requestBody .= "&force=1";
//        }
//        $mac = new Qiniu_Mac($accessKey, $secretKey);
//        $client = new Qiniu_MacHttpClient($mac);
//        list($ret, $err) = Qiniu_Client_CallWithForm($client, $apiHost . $apiPath, $requestBody);
//
//        return ['ret'=>$ret, 'err'=> $err];
//    }
}

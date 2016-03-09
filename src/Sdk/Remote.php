<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/8
 * Time: 下午7:51
 */
namespace Zan\Framework\Sdk;

class Remote
{

    public static $is_verbose = FALSE;

    public static function post($url, $data, $is_build = TRUE, array $setopt = array(), $errorReturn = FALSE)
    {
        $url = self::protocolFix($url);
        self::proxyCheck($url, $setopt);

        $data = ($is_build === TRUE) ? http_build_query($data) : $data;
        $ch = curl_init();
        $default_setopt = array
        (
            //CURLOPT_DNS_USE_GLOBAL_CACHE=> TRUE,					// 启用时会启用一个全局的DNS缓存，此项为线程安全的，并且默认启用。
            //CURLOPT_FOLLOWLOCATION		=> FALSE,					// 启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
            //CURLOPT_FORBID_REUSE		=> TRUE,					// 在完成交互以后强迫断开连接，不能重用。
            CURLOPT_HEADER				=> FALSE,					// 启用时会将头文件的信息作为数据流输出。
            CURLOPT_POST				=> TRUE,					// 启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
            CURLOPT_RETURNTRANSFER		=> TRUE,					// 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
            CURLOPT_SSL_VERIFYPEER		=> FALSE,					// 禁用后cURL将终止从服务端进行验证。使用CURLOPT_CAINFO选项设置证书使用CURLOPT_CAPATH选项设置证书目录 如果CURLOPT_SSL_VERIFYPEER(默认值为2)被启用，CURLOPT_SSL_VERIFYHOST需要被设置成TRUE否则设置为FALSE。
            CURLOPT_VERBOSE				=> Remote::$is_verbose,		// 启用时会汇报所有的信息，存放在STDERR或指定的CURLOPT_STDERR中。
            CURLOPT_FRESH_CONNECT		=> FALSE,
            CURLOPT_URL					=> $url,					// 需要获取的URL地址，也可以在curl_init()函数中设置。
            CURLOPT_POSTFIELDS			=> $data,					// 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
            CURLOPT_TIMEOUT				=> 15,						// 设置cURL允许执行的最长秒数。
            CURLOPT_CONNECTTIMEOUT		=> 0,						// 在发起连接前等待的时间，如果设置为0，则无限等待。
            CURLOPT_DNS_CACHE_TIMEOUT	=> 120,						// 设置在内存中保存DNS信息的时间，默认为120秒。
            CURLOPT_SSL_VERIFYPEER		=> FALSE,
            CURLOPT_FOLLOWLOCATION      => TRUE,
        );
        $setopt = Arr::merge($default_setopt, $setopt);
        curl_setopt_array($ch, $setopt);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno)
        {
            $errStr = curl_error($ch);
            return $errorReturn === FALSE ? "Error: #".$errno . ":" . $errStr : $errorReturn;
        }
        curl_close($ch);
        return $response;
    }

    public static function get($url, array $data = [], array $setopt = array(), $errorReturn = FALSE)
    {
        $url = self::protocolFix($url);
        self::proxyCheck($url, $setopt);

        $ch = curl_init();

        $default_setopt = array
        (
            CURLOPT_HEADER				=> FALSE,
            CURLOPT_RETURNTRANSFER		=> TRUE,
            CURLOPT_VERBOSE				=> Remote::$is_verbose,
            CURLOPT_FRESH_CONNECT		=> FALSE,
            CURLOPT_URL					=> $url . (strpos($url, '?') === FALSE ? '?' : '&') . http_build_query($data),
            CURLOPT_TIMEOUT				=> 15,
            CURLOPT_CONNECTTIMEOUT		=> 0,
            CURLOPT_DNS_CACHE_TIMEOUT	=> 120,
            CURLOPT_SSLVERSION			=> 3,
            CURLOPT_SSL_VERIFYPEER		=> FALSE,
            CURLOPT_FOLLOWLOCATION      => TRUE
        );
        $setopt = Arr::merge($default_setopt, $setopt);
        curl_setopt_array($ch, $setopt);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno)
        {
            return $errorReturn === FALSE ? "Error: #".$errno : $errorReturn;
        }
        curl_close($ch);

        return $response;
    }

    public static function request($url, $data, $type = 'post', $headers = array(), $referer = NULL, $timeout = 5)
    {
        $url = self::protocolFix($url);
        $ch = curl_init();

        if (strtolower($type) == 'post')
        {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);							// 正规的 HTTP POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));				// HTTP POST 内容
        }
        else
        {
            curl_setopt($ch, CURLOPT_URL, $url."?".http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);					// 超时时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);					// 发起连接前等待的时间
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 120);				// 在内存中保存DNS信息的时间
        curl_setopt($ch, CURLOPT_VERBOSE, Remote::$is_verbose);			// 报告每一件意外的事情
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);						// The SSL version (2 or 3) to use.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);					// 返回获取的输出的文本流
        $headers AND curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $referer AND curl_setopt($ch, CURLOPT_REFERER, $referer);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        if ($errno)
        {
            return $errno;
        }
        curl_close($ch);
        return $response;
    }

    /**
     * Return the HTTP status code of a given URI.
     *
     * ###### Using the remote helper:
     *
     *     // Using the remote helper is simple:
     *     echo Kohana::debug(remote::status('http://kohanaphp.com'));
     *
     *     // Output:
     *     (integer) 200
     *
     * @param   string    $url	Fully qualified URI/URL
     * @return  mixed  If no response it returns false
     */
    public static function status($url, $mod = 'status', $limit_time = 5)
    {
        if ( ! Valid::url($url, 'http'))
            return FALSE;

        // Get the hostname and path
        $url = parse_url($url);

        if (empty($url['path']))
        {
            // Request the root document
            $url['path'] = '/';
        }

        // Open a remote connection
        $remote = @ fsockopen($url['host'], 80, $errno, $errstr, $limit_time);

        if ( ! is_resource($remote))
            return FALSE;

        // Set CRLF
        $CRLF = "\r\n";

        // Send request
        fwrite($remote, 'HEAD '.$url['path'].(isset($url['query']) ? '?'.$url['query'] : '').' HTTP/1.0'.$CRLF);
        fwrite($remote, 'Host: '.$url['host'].$CRLF);
        fwrite($remote, 'Connection: close'.$CRLF);
        fwrite($remote, 'User-Agent: mshop (+http://apps.mbaobao.com/)'.$CRLF);

        // Send one more CRLF to terminate the headers
        fwrite($remote, $CRLF);

        while ( ! feof($remote))
        {
            // Get the line
            $line = trim(fgets($remote, 512));

            if ($mod == 'status')
            {
                if ($line !== '' AND preg_match('#^HTTP/1\.[01] (\d{3})#', $line, $matches))
                {
                    // Response code found
                    $response = (int) $matches[1];
                    break;
                }
            }
            elseif ($mod == 'length')
            {
                if ($line !== '' AND preg_match('#^Content-Length: (\d+)#', $line, $matches))
                {
                    // Response code found
                    $response = (int) $matches[1];
                    break;
                }
            }
        }

        // Close the connection
        fclose($remote);

        return isset($response) ? $response : FALSE;
    }

    /**
     * 兼容php5.4~5.6版本上传文件
     * @param $path
     * @return mixed
     */
    public static function fileField($path)
    {
        if (class_exists('\\CURLFile', false))
        {
            return new \CURLFile($path);
        }
        else
        {
            return '@'.$path;
        }
    }

    /**
     * @param $url
     * @return string
     */
    private static function protocolFix($url)
    {
        if (substr($url, 0, 2) == '//')
        {
            $url = 'http:'.$url;
        }
        return $url;
    }

    /**
     * proxy check and request hijack
     * @param $url
     * @param $options
     */
    private static function proxyCheck(&$url, &$options)
    {
        if (isset($options['OPT_USE_YZ_PROXY']) && $options['OPT_USE_YZ_PROXY'] == true)
        {
            $url_parts = parse_url($url);
            if (is_array($url_parts) && isset($url_parts['scheme']) && isset($url_parts['host']))
            {
                // proxy set
                $proxy_list = Config::get('proxy.'.$url_parts['scheme']);
                if ($proxy_list && shuffle($proxy_list))
                {
                    // pick server
                    $proxy_server = current($proxy_list);
                    // force via http
                    $url = 'http://' . $proxy_server . substr($url, strlen($url_parts['scheme']) + 3 + strlen($url_parts['host']));
                    // add host header
                    $options[CURLOPT_HTTPHEADER][] = 'Host:'.$url_parts['host'];
                    // https hack
                    if (strtolower($url_parts['scheme']) === 'https')
                    {
                        // add custom header
                        $options[CURLOPT_HTTPHEADER][] = 'Scheme:https';
                    }
                }
            }
        }
        unset($options['OPT_USE_YZ_PROXY']);
    }

}
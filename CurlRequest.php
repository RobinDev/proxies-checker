<?php
namespace rOpenDev\curl;

/**
 * PHP POO cURL wrapper
 * Make it easy to request a URL (or few)
 * PSR-2 Coding Style, PSR-4 Autoloading
 *
 * @author     Robin <contact@robin-d.fr> http://www.robin-d.fr/
 * @link       https://github.com/RobinDev/curlRequest
 * @since      File available since Release 2014.04.29
 */
class CurlRequest
{

    /**
     * If set to true (via self::setReturnHeaderOnly()), headers only are returned (via self::execute())
     * @var bool
     */
    protected $headerOnly = false;

    /**
     * Curl resource handle
     * @var resource
     */
    public static $ch;

    /*
     * If set to true (via self::setEncodingGzip($gzip)), self::execute() will try to uncompress cURL output
     * @var bool
     */
    protected $gzip=false;

    /**
     * If set to true (via self::setReturnHeader()), self::execute() will extract HTTP header
     * from the cURL output and stock it in self::$header wich can be get with self::getHeader()
     * @var bool
     */
    protected $rHeader=false;

    /**
     * Contain (after self::execute()) header returned by curl request
     * @var string $header
     */
    protected $header;

    /**
     * Constructor
     *
     * @param string $url The URL to request
     * @param bool   $usePreviousSession If the query must use the previous session (so using same connexion if it's the same host)
     */
    public function __construct($url, $usePreviousSession=false)
    {
        if ($usePreviousSession === false || !isset(self::$ch)) {
            self::$ch = curl_init($url);
        }
        else {
            curl_reset(self::$ch);
            $this->setOpt(CURLOPT_URL, $url);
        }
        $this->setOpt(CURLOPT_RETURNTRANSFER, 1);
    }

    /**
     * Change the URL to cURL
     *
     * @param string $url   URL to cURL
     * @param bool   $reset True if you want to remove cURLs params setted before calling this function
     *
     * @return self
     */
    public function setUrl($url, $reset = false)
    {
        if ($reset) {
            curl_reset(self::$ch);
        }
        $this->setOpt(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Add a cURL's option
     *
     * @param int   $option cURL Predefined Constant
     * @param mixed $value
     *
     * @return self
     */
    public function setOpt($option, $value)
    {
        curl_setopt(self::$ch, $option, $value);
        return $this;
    }

    /**
     * A short way to set some classic options to cURL a web page
     *
     * @return self
     */
    public function setDefaultGetOptions($connectTimeOut = 5, $timeOut = 10, $dnsCacheTimeOut = 600, $followLocation = true, $maxRedirs = 5)
    {
        $this->setOpt(CURLOPT_AUTOREFERER,       1)
             ->setOpt(CURLOPT_FOLLOWLOCATION,    $followLocation)
             ->setOpt(CURLOPT_MAXREDIRS,         $maxRedirs)
             ->setOpt(CURLOPT_CONNECTTIMEOUT,    $connectTimeOut)
             ->setOpt(CURLOPT_DNS_CACHE_TIMEOUT, $dnsCacheTimeOut)
             ->setOpt(CURLOPT_TIMEOUT,           $timeOut)
             ->setOpt(CURLOPT_SSL_VERIFYPEER,    0);
        return $this;
    }

    /**
     * A short way to set some classic options to cURL a web page quickly (but lossing some data like header, cookie...)
     *
     * @return self
     */
    public function setDefaultSpeedOptions()
    {
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, 0)
             ->setOpt(CURLOPT_SSL_VERIFYPEER, 0)
             ->setOpt(CURLOPT_HEADER,         0)
             ->setOpt(CURLOPT_COOKIE,         0)
             ->setOpt(CURLOPT_MAXREDIRS,      1);
        return $this;
    }

    /**
     * Call it if you want header informations.
     * After self::execute(), you would have this informations with getHeader();
     *
     * @return self
     */
    public function setReturnHeader()
    {
        $this->setOpt(CURLOPT_HEADER, 1);
        $this->rHeader = true;
        return $this;
    }

    /**
     * Call it if you want header informations only.
     * After self::execute(), you would have this informations with getHeader();
     *
     * @return self
     */
    public function setReturnHeaderOnly()
    {
        $this->headerOnly = true;
        $this->setOpt(CURLOPT_HEADER,    1)
             ->setOpt(CURLOPT_NOBODY,    1);
        return $this;
    }

    /**
     * An self::setOpt()'s alias to add a cookie to your request
     *
     * @param string $cookie
     *
     * @return self
     */
    public function setCookie($cookie)
    {
        $this->setOpt(CURLOPT_COOKIE, $cookie);
        return $this;
    }

    /**
     * An self::setOpt()'s alias to add a referrer to your request
     *
     * @param string $referrer
     *
     * @return self
     */
    public function setReferrer($referrer)
    {
        $this->setOpt(CURLOPT_REFERER, $referrer);
        return $this;
    }

    /**
     * An self::setOpt()'s alias to add an user-agent to your request
     *
     * @param string $ua
     *
     * @return self
     */
    public function setUserAgent($ua)
    {
        $this->setOpt(CURLOPT_USERAGENT, $ua);
        return $this;
    }

    /**
     * An self::setUserAgent()'s alias to add an user-agent wich correspond to a Destkop PC
     *
     * @return self
     */
    public function setDestkopUserAgent()
    {
        $this->setUserAgent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:28.0) Gecko/20100101 Firefox/28.0');
        return $this;
    }

    /**
     * An self::setUserAgent()'s alias to add an user-agent wich correspond to a mobile
     *
     * @return self
     */
    public function setMobileUserAgent()
    {
        $this->setUserAgent('Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        return $this;
    }

    /**
     * An self::setUserAgent()'s alias to add an user-agent wich correspond to a webrowser without javascript
     *
     * @return self
     */
    public function setLessJsUserAgent()
    {
        $this->setUserAgent('NokiaN70-1/5.0609.2.0.1 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1 UP.Link/6.3.1.13.0');
        return $this;
    }

    /**
     * A short way to set post's options to cURL a web page
     *
     * @param array $post_array Contain data (key=>vvalue) to post
     *
     * @return self
     */
    public function setPost($post_array)
    {
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, 1);
        $this->setOpt(CURLOPT_POSTFIELDS, http_build_query($post_array));
        return $this;
    }

    /**
     * If you want to request the URL and hope get the result gzipped.
     * The output will be automatically uncompress with execute();
     *
     * @return self
     */
    public function setEncodingGzip($decode = false)
    {
        $this->setOpt(CURLOPT_ENCODING, 'gzip, deflate');
        $this->gzip = $decode;
        return $this;
    }

    /**
     * If you want to request the URL with a http proxy (public or private)
     *
     * @param string $proxy IP:PORT[:LOGIN:PASSWORD]
     *
     * @return self
     */
    public function setProxy($proxy)
    {
        if (!empty($proxy)) {
            $proxy = explode(':', $proxy);
            $this->setOpt(CURLOPT_HTTPPROXYTUNNEL, 1);
            $this->setOpt(CURLOPT_PROXY, $proxy[0].':'.$proxy[1]);
            if (isset($proxy[2])) {
                $this->setOpt(CURLOPT_PROXYUSERPWD, $proxy[2].':'.$proxy[3]);
            }
        }
        return $this;
    }

    /**
     * Execute the request
     *
     * @return string wich is the request's result without the header (you can obtain with self::getHeader() now)
     */
    public function execute()
    {
        if ($this->headerOnly) {
            return $this->header = curl_exec(self::$ch);
        }
        $html = curl_exec(self::$ch);

        if ($this->gzip && self::gzdecode($html)) {
            $html = self::gzdecode($html);
        }

        if ($this->rHeader) {
            $this->header = substr($html, 0, $sHeader=curl_getinfo(self::$ch, CURLINFO_HEADER_SIZE));
            $html = substr($html, $sHeader);
        }
        return $html;
    }

    /**
     * Return header's data return by the request
     *
     * @return array containing header's data
     */
    public function getHeader()
    {
        if (isset($this->header))
            return self::http_parse_headers($this->header);
    }

    /**
     * Return the cookie(s) returned by the request (if there are)
     *
     * @return null|array containing the cookies
     */
    public function getCookies()
    {
        if (isset($this->header)) {
            $header = $this->getHeader();
            if (isset($header['Set-Cookie'])) {
                return is_array($header['Set-Cookie']) ? implode('; ', $header['Set-Cookie']) : $header['Set-Cookie'];
            }
        }
    }

    /**
     * Return the last error number (curl_errno)
     *
     * @return int the error number or 0 (zero) if no error occurred.
     */
    public function hasError()
    {
        return curl_errno(self::$ch);
    }

    /**
     * Return a string containing the last error for the current session (curl_error)
     *
     * @return string the error message or '' (the empty string) if no error occurred.
     */
    public function getErrors()
    {
        return curl_error(self::$ch);
    }

    /**
     * Get information regarding the request
     *
     * @return bool|array an associative array with the following elements (which correspond to opt), or FALSE on failure
     */
    public function getInfo()
    {
        return curl_getinfo(self::$ch);
    }

    /**
     * Close the connexion
     * Call curl_reset function
     */
    public function close()
    {
        curl_reset(self::$ch);
    }

    /**
     * Parse HTTP headers (php HTTP functions but generally, this packet isn't installed)
     * @source http://www.php.net/manual/en/function.http-parse-headers.php#112917
     *
     * @param string $raw_headers Contain HTTP headers
     *
     * @return bool|array an array on success or FALSE on failure.
     */
    public static function http_parse_headers($raw_headers)
    {
        if (function_exists('http_parse_headers')) {
            http_parse_headers($raw_headers);
        }

        $headers = [];
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1]))  {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else {
                    $headers[$h[0]] = array_merge([$headers[$h[0]]], [trim($h[1])]);
                }

                $key = $h[0];
            }
            else {
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                elseif (!$key)
                    $headers[0] = trim($h[0]);trim($h[0]);
            }
        }

        return $headers;
    }

    /**
     * Decode a string
     *
     * @param string $str String to decode
     */
    public static function gzdecode($str)
    {
        return gzinflate(substr($str,10,-8));
    }
}

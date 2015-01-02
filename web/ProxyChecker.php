<?php
namespace rOpenDev\proxy;

use rOpenDev\curl\CurlRequest;

class ProxyChecker
{

    private $tester;

    private static $headersToScan = [
        'X_FORWARDED_FOR',
        'VIA',
        'via',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION',
        'HTTP_VIA',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR_IP',
    ];

    /**
     * @param string $tester Must contain URL where is served the file `web/test.php`
     */
    public function __construct($tester)
    {
        $this->setTester($tester);
    }

    /**
     * @throws \Exception If $tester is null or empty
     */
    private function setTester($tester)
    {
        if ($tester === null || empty($tester)) {
            throw new \Exception('You MUST set the URL where is served the file `web/test.php`');
        }

        $this->tester = $tester;
    }

    private static function getHostFrom($proxy)
    {
        if ($pos = strpos('://', $proxy)) {
            $proxy = substr($proxy, 0, $pos);
        }

        $proxyPart = explode(':', $proxy);

        return $proxyPart[0];
    }

    /**
     * Test if a HTTP proxy works
     *
     * @param string $proxy
     *
     * @return mixed TRUE if the proxy is ready to use... else the error (transparent or cURL errors)
     */
    public function isProxyValid($proxy) {
        $url = $this->tester;
        $curl = new CurlRequest($url);
        $output = $curl->setDefaultGetOptions()->setDestkopUserAgent()->setProxy($proxy)->execute();
        return $curl->hasError() ? $curl->getErrors() : self::isAnonymeProxy($proxy, $output);
    }

    private static function isAnonymeProxy($proxy, $output)
    {
        $result = json_decode($output, true);

        if (in_array(self::$headersToScan, $result)) {
            return 'transparent';
        }

        $ip = $result['REMOTE_ADDR'];
        return $ip == self::getHostFrom($proxy) ? true : 'transparent';
    }

    /**
     * Test if a proxy is not flagged by Google (captcha or automated request)
     *
     * @param string $proxy
     *
     * @return bool TRUE if is valid for Google... else FALSE
     */
    function isProxyValidForGoogle($proxy)
    {
        $url = 'https://www.google.fr/search?q=site:www.robin-d.fr';
        $curl = new CurlRequest($url);
        $output = $curl->setDefaultGetOptions()
             ->setReturnHeader()
             ->setEncodingGzip()
             ->setOpt(CURLOPT_HTTPHEADER, ['Accept-Language: fr'])
             ->setDestkopUserAgent()
             ->setProxy($proxy)
             ->setReferrer('https://www.google.fr/')
             ->execute();
        return $curl->hasError() || strpos($output, '<title>Sorry...</title>') !== false || strpos($output, 'e=document.getElementById(\'captcha\');if(e){e.focus();}')!==false ? false : true;
    }

}

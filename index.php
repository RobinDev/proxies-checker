<?php
/**
 * Flat PHP Web Interface to check Proxies Validity
 *
 * @author     Robin <contact@robin-d.fr> http://www.robin-d.fr/
 * @source https://github.com/RobinDev/proxies-checker
 * @link   http://proxy.robin-d.fr/
 * @link   http://proxy.robin-d.fr/en/
 * @since  File available since 2014.11.11
 */

/************ Config *****************/
$checkLimit = 5;                                // Nombre de proxies que le script peut checker par requête; 0 = Infini

$baseURL = 'http://proxy.robin-d.fr/';          // URL où est installé votre script

set_time_limit(0);                              // Fixe le temps maximum d'exécution d'un script; 0 = Infini

$lang = [];                                     // Contient le texte insérer dans la page
$lang['tResults']          = 'Results :';
$lang['isOk']              = ' is OK';
$lang['isNotOk']           = ' is not working';
$lang['onlyXChecks']       = 'Nous ne vérifions que '.$checkLimit.' proxies par requête';
$lang['checkingCompleted'] = 'Checking completed';
$lang['noProxies']         = 'No proxy to check';
$lang['title']             = 'Proxies Checker';
$lang['desc']              = '';
$lang['poweredBy']         = 'Powered by';
/************ End Config *****************/



/*********** Logic Helper ****************/
use rOpenDev\curl\CurlRequest;
include 'CurlRequest.php'; // https://github.com/RobinDev/curlRequest

/**
 * Test if a HTTP proxy works
 *
 * @param string $proxy
 *
 * @return mixed TRUE if the proxy is ready to use... else the error (transparent or cURL errors)
 */
function isProxyValid($proxy) {
    global $baseURL;
	$url = $baseURL.'test.php';
    $curl = new CurlRequest($url);
    $output = $curl->setDefaultGetOptions()->setDestkopUserAgent()->setProxy($proxy)->execute();
    $proxy = explode(':', $proxy);
	return $curl->hasError() ? $curl->getErrors() : (trim($output) == $proxy[0] ? true : 'transparent');
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
/************ End Logic Helper *****************/




/************ Logic *****************/
if(isset($_POST['ip_proxy'])) {
    $proxies = explode("\n", trim($_POST['ip_proxy']));
    $n = count($proxies);
    if($n>0&&!empty($proxies)&&!empty($proxies[0])) {
        $msg = $lang['tResults'].'<br>';
        $loop = $checkLimit===0?$n:($n>$checkLimit?$checkLimit:$n);
        for($i=0;$i<$loop;++$i) {
            $proxy = explode(':', trim($proxies[$i]));
            if(count($proxy)>=2 && ($valid=isProxyValid($proxies[$i]))===true) {
                $msg .= '<code>'.$proxies[$i].'</code> '.$lang['isOk'].' (Google : '.(isProxyValidForGoogle($proxies[$i])?'OK':'Kicked').')<br>';
            }
            else {
                $msg .= '<code>'.$proxies[$i].'</code> '.$lang['isNotOk'].(isset($valid) ? ' (<span class=error>'.$valid.'</span>)':'').'<br>';
            }
            unset($valid);
        }
        $msg .= '.:: '.$lang['checkingCompleted'].' '.($n>$checkLimit?'('.$lang['onlyXChecks'].') ':'').'::.';
    }
    else {
         $msg = $lang['noProxies'];
    }
}
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html>
<head><title><?=$lang['title']?></title>
<meta name=viewport content="width=device-width, initial-scale=1.0">
<style>
html{padding-bottom: 120px;min-height: 100%;box-sizing: border-box;position:relative}
a{color:#494949;text-decoration:none}a:hover{color:#999}
body{color:#494949;font-size:1.1em;background-color:#fefefe;min-height:100%;height:100%;width:100%; margin:0}
#header{background:url('data:image/jpeg;base64, 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') repeat; height:100px; width:100%}
#headsep{border-top:10px solid rgba(47, 191, 243, 0.4)}
.center{text-align:center}
p,form{max-width:1000px; color:#494949;margin-left:auto;margin-right:auto}
textarea{background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,0.075) inset;color:#555;display:block;font-size:14px;line-height:1.42857;padding:6px 12px;transition:border-color .15s ease-in-out 0,box-shadow .15s ease-in-out 0;width:100%}textarea:focus{border-color:#66afe9;box-shadow:0 1px 1px rgba(0,0,0,0.075) inset,0 0 8px rgba(102,175,233,0.6);outline:0 none}
.btn {background-color: #51c7f9;border-color: #08a9ed;color: #fff; -moz-user-select: none;background-image: none;border: 1px solid transparent;border-radius: 4px;cursor: pointer;display: inline-block;font-size: 14px;font-weight: 400;line-height: 1.42857;margin-bottom: 0;padding: 6px 12px;text-align: center;vertical-align: middle;white-space: nowrap;}
.btn:hover {background-color: #08a9ed;border-color: #0686bc;color: #fff}
code {background-color: #f9f2f4;border-radius: 4px;color: #c7254e;font-size: 90%;padding: 2px 4px}
.alert{border-left:5px solid #98acc3;padding:10px;background-color:#d9edf7;color:#346597}
.error{font-size:80%}
</style>
<link rel="canonical" href="http://proxy.robin-d.fr/">
<meta name=description content="This proxies checker permits you to check easily if your proxies list is good and valid for Google.">
</head>
<body>
    <div id=header></div>
    <div id=headsep></div>
    <div id=content>
        <h1 class=center><?=$lang['title']?></h1>
        <?=!empty($lang['desc'])?'<p>'.$lang['desc'].'</p>':''?>
        <?=isset($msg)?'<p class=alert>'.$msg.'</p>':''?>
        <form method=POST>
            <textarea name=ip_proxy rows=5 cols=20><?=isset($_POST['ip_proxy'])?$_POST['ip_proxy']:''?></textarea><br>
            <input type="submit" value="Tester les Proxies" class=btn>
        </form>
            <p style="text-align:center;margin-top:300px;"><?=$lang['poweredBy']?> <a href="http://www.robin-d.fr/">Robin (Consultant SEO et Développeur PHP)</a>
        </p>
    </div>
    <a href="https://github.com/RobinDev/proxies-checker"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/52760788cde945287fbb584134c4cbc2bc36f904/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f77686974655f6666666666662e706e67" alt="Fork my proxies checker (PHP script) on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_white_ffffff.png"></a>
</body>

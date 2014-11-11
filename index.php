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
 * @return bool
 */
function isProxyValid($proxy) {
    global $baseURL;
	$url = $baseURL.'test.php';
    $output = $curl->setDefaultGetOptions()->setProxy($proxy)->execute();
    $proxy = explode(':', $proxy);
	return $curl->hasError() ? false : (trim($output) == $proxy[0] ? true : false);
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
            if(count($proxy)>=2 && isProxyValid($proxies[$i])) {
                $msg .= '<code>'.$proxies[$i].'</code> '.$lang['isOk'].' (Google : '.(isProxyValidForGoogle($proxies[$i])?'OK':'Kicked').')<br>';
            }
            else {
                $msg .= '<code>'.$proxies[$i].'</code> '.$lang['isNotOk'].'<br>';
            }
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
body{color:#494949;font-size:1.2em;background-color:#fefefe;min-height:100%;height:100%;width:100%; margin:0}
.center{text-align:center}.text{color:#696969;font-size:.9em}
.hList{padding:0}
@media screen and (min-width: 481px) {.hList>li{display:inline;list-style-type:none;padding-right:1%;padding-left:1%}.hList li+li{border-left:1px solid #696969}}
@media screen and (max-width: 480px) {p{display:none}.hList>li{list-style-type:none;padding-top:5px;padding-bottom:5px}.hList li+li{border-top:1px solid #696969}}
.imgBord{border-radius: 10px; border: 3px solid #fff; }
#header{background:url("http://www.robin-d.fr/images/robin.jpg") repeat; height:100px; width:100%}
#headsep{border-top:10px solid rgba(47, 191, 243, 0.4)}
.inv { margin-left:-10000px; margin-top:-40px}
p,form{max-width:1000px; color:#494949;margin-left:auto;margin-right:auto}
textarea{background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,0.075) inset;color:#555;display:block;font-size:14px;line-height:1.42857;padding:6px 12px;transition:border-color .15s ease-in-out 0,box-shadow .15s ease-in-out 0;width:100%}textarea:focus{border-color:#66afe9;box-shadow:0 1px 1px rgba(0,0,0,0.075) inset,0 0 8px rgba(102,175,233,0.6);outline:0 none}
.btn {background-color: #51c7f9;border-color: #08a9ed;color: #fff; -moz-user-select: none;background-image: none;border: 1px solid transparent;border-radius: 4px;cursor: pointer;display: inline-block;font-size: 14px;font-weight: 400;line-height: 1.42857;margin-bottom: 0;padding: 6px 12px;text-align: center;vertical-align: middle;white-space: nowrap;}
.btn:hover {background-color: #08a9ed;border-color: #0686bc;color: #fff}
code {background-color: #f9f2f4;border-radius: 4px;color: #c7254e;font-size: 90%;padding: 2px 4px}
.alert{border-left:5px solid #98acc3;padding:10px;background-color:#d9edf7;color:#346597}
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

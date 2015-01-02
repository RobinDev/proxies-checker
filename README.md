[Web Proxies Checker : Flat PHP Script](http://proxy.robin-d.fr/)
=================================================================

* Easy to install : just copy and past the `web/` folder
* Check one proxy or a list (valid or not)
* Check if your proxy is not kicked by Google
* Simple & light
* Easy to config and modifiy : just edit `index.php`

##Table of contents
* [Description](#description)
* [Installation](#installation)
* [Demo](#demo)
* [Credits](#credits)
* [Errors](#errors)

##Description

This repo is the code source from my personnal [web proxy checker](http://proxy.robin-d.fr/en/) (or [FR](http://proxy.robin-d.fr/)).
You can use it easily online or install on your own server to configure the script like you want (especially remove limits).

**You can check if your proxies list is working... but not only ! It checks if every proxy can be use on Google or if Google kicked it.**

This script aren't interesting in anonymous, super anonymous, high anonymous. A proxy is just **high anonymous/valid** (no way to retrieve user ip) or **transparent** or **not working**.
If you are interested in a more detailled script to check your proxies, see [Lemoussel's script on SeoBlackOut](http://www.seoblackout.com/2009/08/29/proxy/).

##Installation

[Download](https://github.com/RobinDev/proxies-checker/archive/1.0.3.zip), unzip, **ready to use**.
If you want to edit the configurations, just edit index.php. The config vars are in the first block code delimited by `/***** Config *****/`.

You nead at least a recent PHP version like PHP 5.x.x, php mod cURL and apache2.

**If you are using it locally**, you need to set the `web/test.php` file on a web accessible server and configure the script :
Edit index.php and set the correct URL to access to `web/test.php` file now on a web serve in `$tester` var (l.17).
Eg. `$tester = 'http://example.org/proxies/test.php';`.

##Demo

List of scripts installed by users :
* http://proxy.robin-d.fr/ (online demo, limited use and not updated)
* http://proxy.robin-d.fr/en/ (online demo, limited use and not updated)
* Add your own if it is public by open a [pull-request](https://github.com/RobinDev/proxies-checker/pulls) or Sending me a tweet ([@Robind4](http://twitter.com/Robind4)).

##License

MIT, see the `LICENSE` file.

##Credits

* Original author : [Robin (Consultant SEO)](http://www.robin-d.fr/)
* [CurlRequest](https://github.com/RobinDev/curlRequest) (MIT)

###Contributors :
* ...

## Errors

This part lists errors you can encountered.

### Install on a web server

> ERROR: You need to install this script on an online server or to install the `web/test.php` file on an online server and configure it. See the doc.

If after you copy&paste the `web/` folder into your server web folder (or what you want web folder in your server web folder) you have this error... it's because you are using it locally (and proxy can't get your page `web/test.php` to check IP).

So 2 solutions :
- You moved the script to an online web server
- You moved the `web/test.php` file to an online web server and configure the script (see last line of [Installation Part](#installation))

<?php
function decrypt($encrData, $key, $iv, $ciphering = 'AES-128-CTR') {
    return openssl_decrypt ($encrData, $ciphering, $key, 0, $iv);
}

function encrypt($str, $key, $iv, $ciphering = 'AES-128-CTR') {
    return openssl_encrypt ($str, $ciphering, $key, 0, $iv);
}

function getParamsArray(string $url, bool $withQueryString = true): array
{
    $route = parse_url($url, PHP_URL_PATH);
    $urlParts = array_values(array_filter(explode('/', $route), function($elm){ return !empty($elm); }));
    $params = array_slice($urlParts, 0);

    if ($withQueryString) {
        $queryString = parse_url($url, PHP_URL_QUERY) . '&';
        array_map(function($elm) use (&$params){
            if ($elm != null) {
                $kv = explode('=', $elm);
                if (isset($params[$kv[0]])) {
                    $params[$kv[0]] = (gettype($params[$kv[0]]) === 'array' ? array_merge($params[$kv[0]], [$kv[1]]) : array($params[$kv[0]], $kv[1]));
                } else {
                    $params[$kv[0]] = $kv[1];
                }

            }
        }, explode('&', $queryString));
    }

    return $params;
}

function remoteLastSplash($url)
{
    return substr($url, -1) === '/' ? substr_replace($url, '', -1) : $url;
}

function createNewGuid()
{
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function array2ClassProp(array $data, &$toObject)
{
    foreach ($data as $key=>$value) {
        $toObject->{$key} = $value;
    }
}

function arrayPropertyPathGet(array $arr, $path) {
    $parts = explode('.', $path);
    $ret = $arr;
    foreach($parts as $part) {
        $ret = (mb_ereg_match('^.*_at$', $part) && !empty($ret[$part])) ? (new \DateTime())->setTimestamp($ret[$part])->format('d-m-Y H:i') : ($ret[$part] ?? '');
        //$ret = $ret[$part] ?? '';
    }
    return $ret;
}

function arrayPropertyPathSet(array &$arr, $path, $value) {
    $parts = explode('.', $path);
    $tmp = &$arr;
    foreach($parts as $part) {
        if(!isset($tmp[$part])) { return false; }
        $tmp = &$tmp[$part];
    }
    $tmp = $value;
    return true;
}

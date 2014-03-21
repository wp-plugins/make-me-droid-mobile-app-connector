<?php

function getReadableUserIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) // if from shared
    {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   // if from a proxy
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getUserAgent()
{
    // Extract full UA info
    $ua = ''; // -------> default parameter for User Agent 
     
    $keyname_ua_arr = array(
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_OPERAMINI_PHONE_UA',
        'HTTP_X_OPERAMINI_PHONE',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_X_MOBILE_UA',
        'HTTP_X_SKYFIRE_PHONE',
        'HTTP_USER_AGENT'
    );
    foreach ($keyname_ua_arr as $keyname_ua) {
        if (isset($_SERVER[$keyname_ua]) && !empty($_SERVER[$keyname_ua])) {
            $ua = urlencode($_SERVER[$keyname_ua]);
            break;
        }
    }
    
    return $ua;
}

?>

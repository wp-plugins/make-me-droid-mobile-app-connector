<?php
/*
 * Helper to easily communicate with the MMD API.
 * This is a synchronous call.
 * Requires cURL to be installed on server.
 */
function mmd_wp_call_mmd_api($api, $data, $apiurl)
{  
    $url = $apiurl."?api=".$api."&v=1&".http_build_query($data);
    
    $curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $url);
    curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

    $jsonData = json_decode(curl_exec($curlSession), true);
    curl_close($curlSession);
    
    return $jsonData;
}

?>

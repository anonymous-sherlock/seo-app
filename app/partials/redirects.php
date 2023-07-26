<?php
function checkURLRedirects($url)
{
    $mh = curl_multi_init();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a timeout of 10 seconds

    curl_multi_add_handle($mh, $ch);

    $active = null;
    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh);
        }
    } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

    $info = curl_getinfo($ch);
    $finalURL = $info['url'];

    curl_multi_remove_handle($mh, $ch);
    curl_multi_close($mh);

    $urlWithoutSlash = rtrim($url, '/');
    $finalURLWithoutSlash = rtrim($finalURL, '/');

    return $urlWithoutSlash === $finalURLWithoutSlash ? false : $finalURL;
}

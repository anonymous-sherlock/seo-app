<?php
function checkRobotsTxt($client, $domain)
{
    $robotsTxtPath = 'https://' . $domain . '/robots.txt';
    try {
        $response = $client->head($robotsTxtPath);

        return $response->getStatusCode() === 200;
    } catch (\Exception $e) {
        return false;
    }
}

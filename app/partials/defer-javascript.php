<?php


function getJavaScriptsWithoutDefer($xpath)
{
    $scriptElements = $xpath->query('//script[not(@defer)][@src]');
    $urls = [];
    foreach ($scriptElements as $script) {
        $urls[] = $script->getAttribute('src');
    }
    return $urls;
}

<?php

// extract google tracing id
function extractTrackingID($html)
{
    $matches = [];
    $pattern = '/UA-\d{4,}-\d{1,}/';
    preg_match($pattern, $html, $matches);
    return isset($matches[0]) ? $matches[0] : false;
}

// extract canonical urls
function getCanonicalUrl($xpath)
{
    $canonicalUrlNode = $xpath->evaluate('string((//link[@rel="canonical"]/@href)[1])');
    if (!empty($canonicalUrlNode)) {
        return $canonicalUrlNode; // Return the canonical URL
    }
    return false; // Canonical URL not found
}

// viewport Content
function getViewportContent($xpath)
{
    $viewportMeta = $xpath->query('//meta[@name="viewport"]/@content')->item(0);

    if ($viewportMeta !== null) {
        return $viewportMeta->nodeValue;
    }
    return false; // Viewport meta tag does not exist or does not match the desired attributes
}

// character encoding
function getCharacterEncoding($html)
{
    preg_match('/<meta[^>]+charset=["\']?([a-zA-Z0-9\-_]+)/i', $html, $matches);
    if (isset($matches[1])) {
        return $matches[1]; // Return the character encoding
    }
    return null; // No character encoding declaration found
}



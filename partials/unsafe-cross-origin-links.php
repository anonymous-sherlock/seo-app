<?php

function checkUnsafeCrossOriginLinks($xpath, $currentUrl)
{
    $currentDomain = parse_url($currentUrl, PHP_URL_HOST);

    libxml_use_internal_errors(true); // Ignore any HTML parsing errors
    // Use a cached DOM object if available, or load the HTML file
    $unsafeLinks = [];
    $linkNodes = $xpath->query('//a[@href and string-length(@href) > 0]');
    // Process links in parallel using multiple threads or processes if possible
    foreach ($linkNodes as $linkNode) {
        $href = $linkNode->getAttribute('href');
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            $linkDomain = parse_url($href, PHP_URL_HOST);
            if ($linkDomain !== $currentDomain) {
                // Check for target="_blank" without rel="noopener" or rel="noreferrer"
                $target = $linkNode->getAttribute('target');
                $rel = $linkNode->getAttribute('rel');
                if ($target === '_blank' && (empty($rel) || strpos($rel, 'noopener') === false)) {
                    $unsafeLinks[] = [
                        'url' => $href,
                        'text' => trim(preg_replace('/\s+/', ' ', $linkNode->textContent))
                    ];
                }
            }
        }
    }
    return $unsafeLinks;
}

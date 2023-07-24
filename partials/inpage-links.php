<?php

function isNonSeoFriendlyUrl($url)
{
    // Remove the protocol and www prefix
    $url = preg_replace('/^https?:\/\/(www\.)?/', '', $url);

    // Check for non-SEO friendly patterns
    // '/\b\d{8,}\b/'              Numeric strings with 8 or more digits (e.g., product IDs, timestamps)
    // '/[^a-zA-Z0-9\-\/_.]/'      Non-alphanumeric characters excluding allowed characters
    // '/\d+[A-Za-z]+\d+/',        Alphanumeric strings with numbers and letters combined (e.g., abcd1234)
    // '/[A-Za-z]+\d+[A-Za-z]+/'   Alphanumeric strings with letters and numbers combined (e.g., a1b2c3)

    $pattern = '/\b\d{8,}\b|[^a-zA-Z0-9\-\/_.]|\d+[A-Za-z]+\d+|[A-Za-z]+\d+[A-Za-z]+/';

    return preg_match($pattern, $url) === 1;
}
function extract_links($url, $xpath)
{
    // extract internal and external links 
    $nonSEOFriendlyLinks = [];
    $internalLinks = [];
    $internalLinkUrls = [];
    $externalLinks = [];
    $addedLinks = [];
    $normalizedOriginalUrlHost = strtolower(parse_url($url, PHP_URL_HOST));
    $linkNodes = $xpath->query('//a[not(starts-with(@href, "#"))]');
    foreach ($linkNodes as $linkNode) {
        $href = $linkNode->getAttribute('href');
        $text = trim(str_replace(["\r", "\n", "\t"], '', $linkNode->textContent));

        if (strpos($href, 'mailto:') === 0 || strpos($href, 'tel:') === 0) {
            continue;
        }

        if (!empty($href) && !empty($text)) {
            if (filter_var($href, FILTER_VALIDATE_URL)) {
                $parsedHref = parse_url($href);
                $parsedUrlHost = strtolower($parsedHref['host'] ?? '');

                if ($parsedUrlHost === $normalizedOriginalUrlHost) {
                    $fullUrl = $href;
                    $lowercaseUrl = strtolower($fullUrl);

                    if (!isset($internalLinkUrls[$lowercaseUrl])) {
                        $internalLinks[] = [
                            'url' => $fullUrl,
                            'text' => $text
                        ];

                        $internalLinkUrls[$lowercaseUrl] = true;
                    }
                } else {
                    $fullUrl = rtrim($href, '/');
                    $lowercaseUrl = strtolower($fullUrl);

                    if (!isset($addedLinks[$lowercaseUrl])) {
                        $externalLinks[] = [
                            'url' => $fullUrl,
                            'text' => $text
                        ];

                        $addedLinks[$lowercaseUrl] = true;
                    }
                }
            } else {
                $fullUrl = rtrim($url, '/') . '/' . ltrim($href, '/');
                $lowercaseUrl = strtolower($fullUrl);

                if (!isset($internalLinkUrls[$lowercaseUrl])) {
                    $internalLinks[] = [
                        'url' => $fullUrl,
                        'text' => $text
                    ];

                    $internalLinkUrls[$lowercaseUrl] = true;
                }
            }
        }
    }
    // filter out non seo friendly link from internal array
    $nonSEOFriendlyLinks = array_filter($internalLinks, function ($link) {
        return isNonSeoFriendlyUrl($link['url']);
    });
    $nonSEOFriendlyLinks = $nonSEOFriendlyLinks ? array_column($nonSEOFriendlyLinks, 'url') : false;
    // Prepare the result array
    $result = [
        'nonSEOFriendlyLinks' => $nonSEOFriendlyLinks,
        'internalLinks' => $internalLinks,
        'externalLinks' => $externalLinks
    ];

    return $result;
}

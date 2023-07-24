<?php

function searchMixedContent($document, $url)
{
    $mixedContent = [];
    $total_requests =  0;

    // Search only if secure URL (HTTPS)
    $parsedUrl = parse_url($url);
    $isSecure = isset($parsedUrl['scheme']) && strtolower($parsedUrl['scheme']) === 'https';

    if ($isSecure) {
        foreach ($document->getElementsByTagName('img') as $node) {
            if (!empty($node->getAttribute('src'))) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['images'][] = $src;
                    $total_requests++;
                }
            }
        }

        foreach ($document->getElementsByTagName('script') as $node) {
            if ($node->getAttribute('src')) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['javascript'][] = $src;
                    $total_requests++;
                }
            }
        }

        foreach ($document->getElementsByTagName('link') as $node) {
            if (preg_match('/\bstylesheet\b/', $node->getAttribute('rel'))) {
                $href = $node->getAttribute('href');
                if (stripos($href, 'http://') === 0) {
                    $mixedContent['css'][] = $href;
                    $total_requests++;
                }
            }
        }

        foreach ($document->getElementsByTagName('source') as $node) {
            if (!empty($node->getAttribute('src')) && stripos($node->getAttribute('type'), 'video/') === 0) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['videos'][] = $src;
                    $total_requests++;
                }
            }

            if (!empty($node->getAttribute('src')) && stripos($node->getAttribute('type'), 'audio/') === 0) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['audios'][] = $src;
                    $total_requests++;
                }
            }
        }

        foreach ($document->getElementsByTagName('iframe') as $node) {
            if (!empty($node->getAttribute('src'))) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['iframes'][] = $src;
                    $total_requests++;
                }
            }
        }

        // ... (repeat the same for other elements: script, link, source, iframe, audio)
    }

    // Debug statements
    print_r($mixedContent);

    return [
        'total_requests' => $total_requests,
        'mixedContent' => $mixedContent,
    ];
}

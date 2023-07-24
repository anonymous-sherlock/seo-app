<?php

function searchMixedContent($document, $url)
{
    $mixedContent = [];
    $total_requests =  0;

    // Search only if secure URL (HTTPS)
    $parsedUrl = parse_url($url);
    $isSecure = isset($parsedUrl['scheme']) && strtolower($parsedUrl['scheme']) === 'https';

    if ($isSecure) {

        // Images
        foreach ($document->getElementsByTagName('img') as $node) {
            if (!empty($node->getAttribute('src'))) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['images'][] = $src;
                    $total_requests++;
                }
            }
        }

        // JavaScript
        foreach ($document->getElementsByTagName('script') as $node) {
            if ($node->getAttribute('src')) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['javascript'][] = $src;
                    $total_requests++;
                }
            }
        }

        // CSS (Stylesheets)
        foreach ($document->getElementsByTagName('link') as $node) {
            if (preg_match('/\bstylesheet\b/', $node->getAttribute('rel'))) {
                $href = $node->getAttribute('href');
                if (stripos($href, 'http://') === 0) {
                    $mixedContent['css'][] = $href;
                    $total_requests++;
                }
            }
        }

        // Videos
        foreach ($document->getElementsByTagName('source') as $node) {
            if (!empty($node->getAttribute('src')) && stripos($node->getAttribute('type'), 'video/') === 0) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['videos'][] = $src;
                    $total_requests++;
                }
            }
        }

        // Audios
        foreach ($document->getElementsByTagName('source') as $node) {
            if (!empty($node->getAttribute('src')) && stripos($node->getAttribute('type'), 'audio/') === 0) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['audios'][] = $src;
                    $total_requests++;
                }
            }
        }

        // Iframes
        foreach ($document->getElementsByTagName('iframe') as $node) {
            if (!empty($node->getAttribute('src'))) {
                $src = $node->getAttribute('src');
                if (stripos($src, 'http://') === 0) {
                    $mixedContent['iframes'][] = $src;
                    $total_requests++;
                }
            }
        }

        // Anchor links
        foreach ($document->getElementsByTagName('a') as $node) {
            if (!empty($node->getAttribute('href'))) {
                $href = $node->getAttribute('href');
                if (stripos($href, 'http://') === 0) {
                    $mixedContent['links'][] = $href;
                    $total_requests++;
                }
            }
        }
    }

    // Debug statements
    print_r($mixedContent);

    return [
        'total_requests' => $total_requests,
        'mixedContent' => $mixedContent,
    ];
}

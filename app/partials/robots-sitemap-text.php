<?php

function checkRobotsAndSitemap($domainUrl, $baseUrl, $client)
{
    $sitemaps = null;
    $disallowRules = [];
    $isDisallowed = [];

    // Fetch robots.txt data directly
    $robotsUrl = rtrim($domainUrl, '/') . '/robots.txt';
    try {
        $robotsRequest = $client->get($robotsUrl);
        $robotsResponse = $robotsRequest->getBody()->getContents();
    } catch (\Exception $e) {
        $robotsResponse = null; // Return null in case of any exception
    }

    if ($robotsResponse) {
        if (strpos($robotsResponse, 'Sitemap:') !== false) {
            preg_match_all('/Sitemap:\s*([^\r\n]+)/i', $robotsResponse, $matchs);
            $sitemaps = $matchs[1] ?? false;
        }

        if (preg_match_all('/^Disallow:\s*([^#\s]*)/im', $robotsResponse, $matches)) {
            // Filter out any entries that contain "Sitemap:"
            $disallowRules = array_filter($matches[1], function ($directive) {
                return !preg_match('/Sitemap:/i', $directive);
            });
        }

        foreach ($disallowRules as $directive) {
            $formattedValue = formatRobotsRule($directive);

            // Check if the directive is not empty and matches the base URL
            if (!empty($directive) && preg_match($formattedValue, $baseUrl)) {
                $isDisallowed[] = $directive;
            }
        }
    }

    return [
        'has_robots_txt' => !empty($robotsResponse),
        'disallow_rules' => $disallowRules,
        'disallowed' => $isDisallowed,
        'sitemaps' => $sitemaps,
    ];
}
function formatRobotsRule($value)
{
    $before = ['*' => '__ASTERISK', '$' => '__DOLLAR'];
    $after = ['__ASTERISK' => '.*', '__DOLLAR' => '$'];

    return '/' . str_replace(array_keys($after), array_values($after), preg_quote(str_replace(array_keys($before), array_values($before), $value), '/')) . '/i';
}

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

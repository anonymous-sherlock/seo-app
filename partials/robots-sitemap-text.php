<?php

function checkRobotsAndSitemap()
{
    $sitemaps = null;
    $cacheKey = md5($this->domainUrl) . Str::random(3) . "-robots";
    $disallowRules = [];
    $isDisallowed = [];
    $robotsResponse = Cache::rememberForever($cacheKey, function () {
        $robotsUrl =  Str::of($this->domainUrl)->finish('/')->finish('robots.txt')->toString();
        try {
            $robotsRequest = $this->client->get($robotsUrl);
            $robotsResponse = $robotsRequest->getBody()->getContents();

            return $robotsResponse;
        } catch (\Exception $e) {
        }
    });

    if ($robotsResponse) {
        if (Str::contains($robotsResponse, 'Sitemap:')) {
            preg_match_all('/Sitemap: ([^\r\n]*)/', $robotsResponse, $matchs);
            $sitemaps = $matchs[1] ?? false;
        }

        if (Str::of($robotsResponse)->lower()->contains('disallow:')) {
            preg_match_all('/Disallow: ([^\r\n]*)/', $robotsResponse, $robotsRules);
            foreach ($robotsRules[0] as $robotsRule) {
                $rule = Str::of($robotsRule)->lower()->explode(':', 2);
                $directive = trim($rule[0] ?? null);
                $value = trim($rule[1] ?? null);

                if ($directive == 'disallow' && $value) {
                    $disallowRules[] = $value;
                    if (preg_match($this->formatRobotsRule($value), $this->baseUrl)) {
                        $isDisallowed[] = $value;
                    }
                }
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

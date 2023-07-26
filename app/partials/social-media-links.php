<?php
function getSocialMediaProfiles($xpath)
{
    $socialProfiles = [];

    // Define the social media platforms and their associated domain names
    $socialPlatforms = [
        'facebook' => 'facebook.com',
        'twitter' => 'twitter.com',
        'instagram' => 'instagram.com',
        'linkedin' => 'linkedin.com',
        'youtube' => 'youtube.com',
        'pinterest' => 'pinterest.com',
        'snapchat' => 'snapchat.com',
        'tiktok' => 'tiktok.com',
        'reddit' => 'reddit.com',
        'tumblr' => 'tumblr.com',
        'github' => 'github.com',
        'wordpress' => 'wordpress.com',
        'soundcloud' => 'soundcloud.com',
        'pexels' => 'pexels.com',
        'behance' => 'behance.net',
        'dribbble' => 'dribbble.com',
        'deviantart' => 'deviantart.com',
        'flickr' => 'flickr.com',
        'vimeo' => 'vimeo.com',
        'twitch' => 'twitch.tv',
        'spotify' => 'spotify.com',
        'medium' => 'medium.com',
        'weibo' => 'weibo.com',
        'vk' => 'vk.com',
        'telegram' => 'telegram.org',
        'slack' => 'slack.com',
        'digg' => 'digg.com',
        'quora' => 'quora.com',
        // Add more social media platforms here
    ];

    // Extract all anchor nodes from the HTML
    $anchorNodes = $xpath->query('//a');

    // Iterate over the anchor nodes and extract social media profiles
    foreach ($anchorNodes as $anchorNode) {
        $href = $anchorNode->getAttribute('href');
        if (!empty($href)) {
            foreach ($socialPlatforms as $platform => $domain) {
                if (strpos($href, $domain) !== false) {
                    $socialProfiles[$platform] = $href;
                    break; // Found the platform, no need to check other platforms
                }
            }
        }
    }

    return $socialProfiles;
}

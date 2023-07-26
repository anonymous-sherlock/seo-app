<?php

function extractSocialMediaMetaTags($xpath)
{
    $metaTags = $xpath->query('/html/head/meta');

    $socialMediaMetaTags = array(
        'openGraph' => null,
        'twitterCard' => null,
        'facebook' => null,
        'pinterest' => null,
        'linkedin' => null,
        'instagram' => null,
        'googlePlus' => null
    );

    foreach ($metaTags as $metaTag) {
        $property = $metaTag->getAttribute('property');
        $name = $metaTag->getAttribute('name');
        $content = $metaTag->getAttribute('content');

        switch (true) {
            case (strpos($property, 'og:') === 0):
                $socialMediaMetaTags['openGraph'][$property] = $content;
                break;
            case (strpos($name, 'twitter:') === 0):
                $socialMediaMetaTags['twitterCard'][$name] = $content;
                break;
            case (strpos($property, 'fb:') === 0):
                $socialMediaMetaTags['facebook'][$property] = $content;
                break;
            case ($name === 'pinterest-rich-pin'):
                $socialMediaMetaTags['pinterest'][$name] = $content;
                break;
            case (strpos($property, 'linkedin:') === 0):
                $socialMediaMetaTags['linkedin'][$property] = $content;
                break;
            case ($name === 'instagram:app_id'):
                $socialMediaMetaTags['instagram'][$name] = $content;
                break;
            case (strpos($name, 'google+:') === 0):
                $socialMediaMetaTags['googlePlus'][$name] = $content;
                break;
        }
    }

    foreach ($socialMediaMetaTags as &$value) {
        if (empty($value)) {
            $value = false;
        }
    }

    return $socialMediaMetaTags;
}

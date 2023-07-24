<?php

function fixUrl($url)
{
    // You can implement your logic to fix URLs if needed.
    // For demonstration purposes, let's return the original URL.
    return $url;
}

function checkImageFormats(array $imageUrls)
{
    $imageFormats = [];
    // Accepted nextgen formats
    $formats = ['webp', 'avif', 'svg'];
    $urlFormats = ['=webp', '=avif', 'data:image/svg'];

    foreach ($imageUrls as $imageUrl) {
        $extension = mb_strtolower(pathinfo(fixUrl($imageUrl), PATHINFO_EXTENSION));
        if (!in_array($extension, $formats) && !containsAny($imageUrl, $urlFormats)) {
            $imageFormats[] = [
                'url' => fixUrl($imageUrl),
                'format' => $extension,
            ];
        }
    }

    return array_unique($imageFormats, SORT_REGULAR);
}

function containsAny($haystack, array $needles)
{
    foreach ($needles as $needle) {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }
    }
    return false;
}

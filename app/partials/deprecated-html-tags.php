<?php

function checkDeprecatedHTMLTags($xpath)
{
    // Define the deprecated HTML tags
    $deprecatedTags = [
        'acronym',
        'applet',
        'basefont',
        'big',
        'center',
        'dir',
        'font',
        'frame',
        'frameset',
        'isindex',
        'noframes',
        's',
        'strike',
        'tt',
        'u',
        'xmp',
        // Add more deprecated tags here
    ];

    $deprecatedTagCounts = [];

    // Construct the XPath query to select all deprecated tags at once
    $query = "//" . implode(" | //", $deprecatedTags);
    $tagNodes = $xpath->query($query);

    // Count the occurrences of each deprecated tag
    foreach ($tagNodes as $tagNode) {
        $tagName = $tagNode->tagName;
        $deprecatedTagCounts[$tagName] = isset($deprecatedTagCounts[$tagName]) ? $deprecatedTagCounts[$tagName] + 1 : 1;
    }

    return $deprecatedTagCounts;
}

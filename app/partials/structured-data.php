<?php

function extractStructuredData($xpath)
{
    // Select the elements containing structured data using XPath
    $nodes = $xpath->query('//script[@type="application/ld+json"]');
    // Array to store the extracted structured data
    $structuredData = [];


    foreach ($nodes as $node) {
        $scriptContent = $node->nodeValue;
        $jsonLdData = json_decode($scriptContent, true);
        if ($jsonLdData !== null && isset($jsonLdData['@context']) && $jsonLdData['@context'] === 'https://schema.org') {
            $structuredData['Schema.org'] = $jsonLdData;
        }
    }
    return $structuredData;
}

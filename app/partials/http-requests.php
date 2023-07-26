<?php
function getHttpRequestsByType($dom)
{
    $requests = [
        'totalRequests' => 0,
        'Resources' => [
            'images' => [],
            'javascript' => [],
            'css' => [],
        ],
    ];

    $resourceTypes = [
        'images' => ['img', 'src'],
        'javascript' => ['script', 'src'],
        'css' => ['link', 'href', 'rel', 'stylesheet'],
    ];

    foreach ($resourceTypes as $resourceType => $attributes) {
        $nodes = $dom->getElementsByTagName($attributes[0]);
        foreach ($nodes as $node) {
            $attributeValue = $node->getAttribute($attributes[1]);
            if (!empty($attributeValue) && !in_array($attributeValue, $requests['Resources'][$resourceType])) {
                if (count($attributes) > 2) {
                    $rel = $node->getAttribute($attributes[2]);
                    if (count($attributes) > 3 && $rel !== $attributes[3]) {
                        continue;
                    }
                }
                $requests['Resources'][$resourceType][] = $attributeValue;
                $requests['totalRequests']++;
            }
        }
    }
    return $requests;
}

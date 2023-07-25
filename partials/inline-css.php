<?php
// function doInlineCSS($document)
// {
//     $inlineCss = [];
//     foreach ($document->getElementsByTagName('*') as $node) {
//         if ($node->nodeName != 'svg' && !empty($node->getAttribute('style'))) {
//             $inlineCss[] = $node->getAttribute('style');
//         }
//     }

//     return $inlineCss;
// }


function extractInlineCSS($xpath)
{
    $styles = [];

    // Query the "style" attribute of elements
    $styleAttributes = $xpath->query('//*[@style]/@style');

    // Extract the inline CSS from the attribute values
    foreach ($styleAttributes as $styleAttribute) {
        $style = $styleAttribute->nodeValue;
        if (!empty($style)) {
            $styles[] = $style;
        }
    }

    return $styles;
}

<?php
function hasMetaTag($xpathGlobal, $attribute, $values)
{
    $xpath = $xpathGlobal;
    $query = "//meta[@{$attribute}='robots']";
    $metaTags = $xpath->query($query);
    $noIndexContents = [];
    if ($metaTags->length > 0) {
        $content = $metaTags->item(0)->getAttribute('content');
        foreach ($values as $value) {
            if (strpos($content, $value) !== false) {
                if ($value === "noindex") {
                    $noIndexContents[] = $content;
                    return $noIndexContents;
                }
                return true; // Meta tag exists
            }
        }
    }
    return false; // Meta tag does not exist or values not found
}

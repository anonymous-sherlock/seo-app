<?php
require_once __DIR__ . '/utils/find-keywords.php';
require_once __DIR__ . '/utils/get-text-content.php'; 

function doImageResult($document)
{
    $elements = $document->getElementsByTagName('img');

    $content = [];
    $images = [];
    foreach ($elements as $element) {
        $img = [
            'src'   => $element->getAttribute('src') ?: $element->getAttribute('data-src') ?: $element->getAttribute('data-delayed-url') ?: '',
            'alt'   => $element->getAttribute('alt') ?: null,
            'title' => $element->getAttribute('title') ?: null,
        ];
        if (empty($img['src'])) {
            continue;
        }

        if (!empty($img['alt'])) {
            $content[] = getTextContent($img['alt']);
        }

        $images[] = $img;
    }

    $txt = implode(' ', $content);

    return [
        'count'            => count($images),
        'count_alt'        => count($content),
        'words'            => count(str_word_count(strtolower($txt), 1)),
        'keywords'         => findKeywords($txt, 1),
        // 'longTailKeywords' => getLongTailKeywords($content, 2, 2),
        'images'           => $images,
    ];
}

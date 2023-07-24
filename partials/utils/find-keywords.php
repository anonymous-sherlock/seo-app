<?php
function findKeywords($content, $min = 3, $stopWords = [])
{
    $words = str_word_count(strtolower($content), 1);

    $word_count = array_count_values($words);
    arsort($word_count);

    foreach ($stopWords as $s) {
        unset($word_count[$s]);
    }

    $word_count = array_filter($word_count, function ($value) use ($min) {
        return $value >= $min;
    });

    return $word_count;
}

<?php

function getLongTailKeywords($strs, $len = 3, $min = 3, $limit = 15)
{
    $keywords = [];
    if (!is_array($strs)) {
        $strs = [$strs];
    }

    foreach ($strs as $str) {
        $str = preg_replace('/[^a-z0-9\s-]+/', '', strtolower($str));
        $str = preg_split('/\s+-\s+|\s+/', $str, -1, PREG_SPLIT_NO_EMPTY);
        while (0 < $len--) {
            for ($i = 0; $i < count($str) - $len; $i++) {
                $word = array_slice($str, $i, $len + 1);
                if (in_array($word[0], $this->stopWords) || in_array(end($word), $this->stopWords)) {
                    continue;
                }

                $word = implode(' ', $word);

                if (!isset($keywords[$len][$word])) {
                    $keywords[$len][$word] = 0;
                }

                $keywords[$len][$word]++;
            }
        }
    }

    $return = [];
    foreach ($keywords as $keyword) {
        $keyword = array_filter($keyword, function ($v) use ($min) {
            return $v >= $min;
        });
        arsort($keyword);
        $return = array_merge($return, $keyword);
    }

    return collect($return)->take($limit)->toArray();
}

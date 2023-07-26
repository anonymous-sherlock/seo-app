<?php

class MostCommonKeywordsAnalyzer
{
    private $stopWords;

    public function __construct($stopwordFile)
    {
        $this->loadStopWords($stopwordFile);
    }

    private function loadStopWords($stopwordFile)
    {
        $stopWordsData = file_get_contents($stopwordFile);
        $this->stopWords = json_decode($stopWordsData, true);
    }

    private function extractTextFromNode(DOMNode $node)
    {
        $text = '';

        if ($node instanceof DOMText) {
            // If the node is a text node, append its value to the extracted text
            $text .= $node->nodeValue;
        } elseif ($node instanceof DOMElement && !in_array($node->nodeName, ['script', 'style'])) {
            // If the node is an element node (not 'script' or 'style'), process its child nodes
            foreach ($node->childNodes as $childNode) {
                $text .= $this->extractTextFromNode($childNode);
            }
        }

        return $text;
    }

    private function extractTextFromHTML($html)
    {
        // Create a DOMDocument object and suppress errors for invalid HTML
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        // Extract the text from the HTML using the recursive function
        $text = $this->extractTextFromNode($dom->documentElement);

        // Remove extra spaces and newlines
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return $text;
    }

    private function cleanText($text)
    {
        // Convert the text to lowercase for case-insensitive matching
        $text = strtolower($text);

        // Remove Tags from HTML
        $text = strip_tags($text);

        // Remove non-alphanumeric characters except spaces
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return $text;
    }

    public function analyzeHTML($htmlContent)
    {
        // Extract the text and calculate the word count
        $text = $this->extractTextFromHTML($htmlContent);
        $contentLength = str_word_count($text);

        // Clean the text
        $text = $this->cleanText($text);

        // Split the text into words
        $words = explode(' ', $text);

        // Calculate word count
        $wordCount = array_count_values(array_diff($words, $this->stopWords));
        arsort($wordCount);

        // Get keywords with count for counts >= 3
        $keywordsWithCount = array_filter($wordCount, fn($count) => $count >= 3);

        return [
            'contentLength' => $contentLength,
            'keywordsWithCount' => $keywordsWithCount,
        ];
    }
}
<?php

require_once __DIR__ . '/partials/most-common-keywords.php';
require_once __DIR__ . '/partials/404-page.php';


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WebsiteSEOChecker
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function checkSEO($url)
    {
        try {
            // Validate and sanitize the URL input
            $url = filter_var($url, FILTER_VALIDATE_URL);
            if (!$url) {
                throw new InvalidArgumentException('Please Enter a Valid URL.');
            }

            // Fetch the HTML content of the provided URL
            $html = $this->fetchHTML($url);

            // Process the HTML content and extract SEO information
            $seoInfo = $this->processHTML($html);
            // Analyze the HTML and get the most common keywords

            $starttime = microtime(true);
            $analyzer = new MostCommonKeywordsAnalyzer('partials/stopwords/en.json');
            $keywordsInfo = $analyzer->analyzeHTML($html);
            $isCustom404Page = is404Page($this->client, $url);

            $seoInfo['404Page'] = $isCustom404Page;
            // Append the keywords information to the $seoInfo array
            $seoInfo['contentLength'] = $keywordsInfo['contentLength'];
            $seoInfo['commonKeywords'] = $keywordsInfo['keywordsWithCount'];
            $endtime = microtime(true);
            $executionTime = $endtime - $starttime;
            // echo "Execution time: " . $executionTime . " seconds\n" . PHP_EOL;


            return $seoInfo;
        } catch (RequestException $e) {
            // Log the error and return an error response
            error_log('Error fetching website content: ' . $e->getMessage());
            throw new RuntimeException('Cannot Check SEO of This Website. Try Again After Some Time');
        }
    }

    private function fetchHTML($url)
    {
        $response = $this->client->get($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36',
                'Accept-Encoding' => 'gzip',
                // Enable gzip compression
            ],
            'timeout' => 5,
            // Set a timeout of 5 seconds
            'http_version' => '2.0', // Use HTTP/2.0 if supported
        ]);

        $contentEncoding = $response->getHeaderLine('Content-Encoding');
        $body = (string) $response->getBody();

        // Check if the response is gzipped
        if ($contentEncoding === 'gzip') {
            // Uncompress the gzipped content
            $body = gzdecode($body);
        }

        return $body;
    }

    private function processHTML($html)
    {
        libxml_use_internal_errors(true); // Ignore any HTML parsing errors
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);
        // Create a DOMXPath object to query the DOM
        $xpath = new DOMXPath($dom);
        // response variable intialize
        $seoInfo = [];

        $seoInfo['hasDoctype'] = strpos($html, '<!DOCTYPE html>') !== false;
        $seoInfo['language'] = $dom->documentElement->getAttribute('lang');
        $seoInfo['framesets'] = $xpath->evaluate('count(//frameset) > 0') ?: false;
        $faviconNode = $xpath->query('/html/head/link[@rel="icon" or @rel="shortcut icon"]/@href')->item(0);
        if ($faviconNode) {
            $seoInfo['favicon'] = $faviconNode->nodeValue;
        }
        $titleNode = $xpath->query('/html/head/title')->item(0);
        $seoInfo['title'] = $titleNode ? $titleNode->nodeValue : false;
        // meta description
        $descriptionNode = $xpath->query('/html/head/meta[@name="description"]/@content')->item(0);
        $seoInfo['description'] = $descriptionNode ? $descriptionNode->nodeValue : false;
        // Extract meta keywords
        $keywordsNode = $xpath->query('/html/head/meta[@name="keywords"]/@content')->item(0);
        $seoInfo['metaKeywords'] = $keywordsNode ? $keywordsNode->nodeValue : false;
        // Headings
        $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $seoInfo['headings'] = [];
        foreach ($headings as $heading) {
            $headingNodes = $xpath->query("//{$heading}");
            foreach ($headingNodes as $headingNode) {
                $text = trim($headingNode->textContent);
                $seoInfo['headings'][$heading][] = $text;
            }
        }
        // image extract 
        $imageNodes = $xpath->query('//img[@src]');
        $seoInfo['totalImageCount'] = $imageNodes->length;
        foreach ($imageNodes as $imageNode) {
            $alt = $imageNode->getAttribute('alt');
            if (empty($alt)) {
                $seoInfo['imagesWithoutAltText'][] = $imageNode->getAttribute('src');
            }
        }

        // Add more code here to extract other SEO information as needed
        return $seoInfo;
    }
}

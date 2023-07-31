<?php
// $files = glob(__DIR__ . '/partials/*.php');

// $filesToExclude = [
//     // 'deprecated-html-tags.php',
//     // Add more file names to exclude here...
// ];

// foreach ($files as $file) {
//     $fileName = basename($file);
//     // Check if the file should be excluded
//     if (!in_array($fileName, $filesToExclude)) {
//         require_once $file;
//     }
// }

require_once __DIR__ . '/partials/utils/registery.php';
require_once __DIR__ . '/partials/most-common-keywords.php';
require_once __DIR__ . '/partials/404-page.php';
require_once __DIR__ . '/partials/inpage-links.php';
require_once __DIR__ . '/partials/robots-sitemap-text.php';
require_once __DIR__ . '/partials/meta-robots.php';
require_once __DIR__ . '/partials/spf-record.php';
require_once __DIR__ . '/partials/http-requests.php';
require_once __DIR__ . '/partials/modern-image-formats.php';
require_once __DIR__ . '/partials/redirects.php';
require_once __DIR__ . '/partials/defer-javascript.php';
require_once __DIR__ . '/partials/deprecated-html-tags.php';
require_once __DIR__ . '/partials/framesets-and-nested-tables.php';
require_once __DIR__ . '/partials/plain-text-email.php';
require_once __DIR__ . '/partials/check-ssl.php';
require_once __DIR__ . '/partials/mixed-content.php';
require_once __DIR__ . '/partials/unsafe-cross-origin-links.php';
require_once __DIR__ . '/partials/social-media-links.php';
require_once __DIR__ . '/partials/social-media-links.php';
require_once __DIR__ . '/partials/structured-data.php';
require_once __DIR__ . '/partials/social-media-meta-tags.php';
require_once __DIR__ . '/partials/inline-css.php';
require_once __DIR__ . '/partials/analytics.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;



class WebsiteSEOChecker
{
    private $client;
    private $dom;
    private $xpath;
    private $loadtime = 0;
    private $domainUrl = null;
    private $server = null;
    private $encoding = null;
    private $http2 = false;
    private $hsts = false;

    public function __construct()
    {
        $this->client = new Client();
        $this->dom = new DOMDocument();
    }

    private function loadHTML($html)
    {
        libxml_use_internal_errors(true); // Ignore any HTML parsing errors
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = false;
        $this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); // Add options here
        libxml_use_internal_errors(false);
        $this->xpath = new DOMXPath($this->dom);
    }

    public function checkSEO($url)
    {
        try {
            // Validate and sanitize the URL input
            $url = filter_var($url, FILTER_VALIDATE_URL);
            if (!$url) {
                throw new InvalidArgumentException('Please Enter a Valid URL.');
            }
            // Extract the domain from the URL
            $domain = parse_url($url, PHP_URL_HOST);
            $this->domainUrl = $domain;
            // base url

            // Fetch the HTML content of the provided URL
            $seoInfo = [];
            $pageStart = microtime(true);
            $html = $this->fetchHTML($url);
            $pageEnd = microtime(true);
            $this->loadtime = round($pageEnd - $pageStart, 2);;
            // Process the HTML content and extract SEO information
            $this->loadHTML($html);

            $s = microtime(true);

            $seoInfo['url'] = $url;
            $seoInfo['domain'] = $domain;
            $seoInfo['canonical'] = getCanonicalUrl($this->xpath);
            $seoInfo['pageSize'] = mb_strlen($html, '8bit');
            $seoInfo['redirects'] = checkURLRedirects($url);
            $seoInfo['sitemap'] = checkRobotsAndSitemap($domain, $url, $this->client);
            $seoInfo['loadtime'] = $this->loadtime;
            $seoInfo['encoding'] = $this->encoding;
            $seoInfo['server'] = $this->server;
            $seoInfo['http2'] = $this->http2;
            $seoInfo['hsts'] = $this->hsts;
            $seoInfo['viewport'] = getViewportContent($this->xpath);
            $seoInfo['characterEncoding'] = getCharacterEncoding($html);
            $seoInfo['googleTrackingID'] = extractTrackingID($html);
            $seoInfo['nonDeferJs'] = getJavaScriptsWithoutDefer($this->xpath);
            $seoInfo['nestedTables'] = nestedTablesTest($this->xpath);
            $seoInfo['framesets'] = framesetsTest($this->xpath);
            $seoInfo['plainTextEmail'] = plainTextEmail($this->xpath);
            $seoInfo['ssl'] = getSSLCertificateInfo($domain);
            $seoInfo['mixedContent'] = searchMixedContent($this->dom, $url);
            $seoInfo['unsafeLinks'] = checkUnsafeCrossOriginLinks($this->xpath, $url);
            $seoInfo['Socails'] = getSocialMediaProfiles($this->xpath);
            $seoInfo['socialMetaTags'] = extractSocialMediaMetaTags($this->xpath);
            $seoInfo['structuredData'] = extractStructuredData($this->xpath);
            $seoInfo['deprecatedTags'] = checkDeprecatedHTMLTags($this->xpath);
            $seoInfo['inlineCss'] = extractInlineCSS($this->xpath);




            // Debug: Check nested tables with XPath query
            // $seoInfo['mixedContent'] = 
            $e = microtime(true);
            $exet = $e - $s;
            // echo 'Execution time is ' . $exet . PHP_EOL;
            $seoInfo = $this->processHTML($seoInfo);






            // Analyze the HTML and get the most common keywords
            $stopwordFile = __DIR__ . '/partials/stopwords/en.json';
            $analyzer = new MostCommonKeywordsAnalyzer($stopwordFile);
            $keywordsInfo = $analyzer->analyzeHTML($html);

            $seoInfo['404Page'] = is404Page($this->client, $url);
            $seoInfo['contentLength'] = $keywordsInfo['contentLength'];
            $seoInfo['robotsTxt'] = checkRobotsTxt($this->client, $domain);
            $seoInfo['noFollow'] = hasMetaTag($this->xpath, 'name', ['nofollow']);
            $seoInfo['noIndex'] = hasMetaTag($this->xpath, 'name', ['noindex']);
            $seoInfo['spfRecord'] = getSPFRecord($domain);


            $extractLinks = extract_links($url, $this->xpath);

            $seoInfo['commonKeywords'] = $keywordsInfo['keywordsWithCount'];
            $seoInfo['nonSEOFriendlyLinks'] = $extractLinks['nonSEOFriendlyLinks'];
            $seoInfo['internalLinks'] = $extractLinks['internalLinks'];
            $seoInfo['externalLinks'] = $extractLinks['externalLinks'];
            $seoInfo['httpRequests'] = getHttpRequestsByType($this->dom);





            return $seoInfo;
        } catch (RequestException $e) {
            // Log the error and return an error response
            error_log('Error fetching website content: ' . $e->getMessage());
            throw new RuntimeException('Cannot Check SEO of This Website. Try Again After Some Time');
        }
    }

    private function fetchHTML($url)
    {
        try {
            $response = $this->client->get(
            $url,
            [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36',
                    'Accept-Encoding' => 'gzip',
                    // Enable gzip compression
                ],
                'on_stats' => function (TransferStats $stats) {
                    $this->http2 = $stats->getHandlerStat('http_version') === 2;
                },
                'on_headers' => function (ResponseInterface $response) {
                    $servers = array_filter($response->getHeader('server'), function ($value) {
                        return !in_array($value, ['amazon', 'cloudflare', 'gws', 'Server', 'Apple', 'tsa_o', 'ATS']);
                    });
                    $this->hsts = count($response->getHeader('Strict-Transport-Security')) !== 0;
                    $this->server = $servers;
                    $this->encoding = $response->getHeader('x-encoded-content-encoding');
                },

                'timeout' => 5,
                // Set a timeout of 5 seconds
                'http_version' => '2.0',
                // Use HTTP/2.0 if supported
            ]
        );

        $contentEncoding = $response->getHeaderLine('Content-Encoding');
        $body = (string) $response->getBody();

        // Check if the response is gzippedcontent
        if ($contentEncoding === 'gzip') {
            // Uncompress the gzipped content
            $body = gzdecode($body);
        }

        return $body;
        } catch (\Throwable $th) {
            echo json_encode(['error' => 'Bad Request: Request Timed Out']);
            exit;
        }
       
    }

    private function processHTML(&$seoInfo)
    {
        // response variable initialize
        $seoInfo['domSize'] = count($this->dom->getElementsByTagName('*'));
        $seoInfo['hasDoctype'] = strpos($this->dom->saveHTML(), '<!DOCTYPE html>') !== false;
        $seoInfo['language'] = $this->dom->documentElement->getAttribute('lang');

        // Use $this->xpath directly for querying
        $faviconNode = $this->xpath->query('/html/head/link[@rel="icon" or @rel="shortcut icon"]/@href')->item(0);
        if ($faviconNode) {
            $seoInfo['favicon'] = $faviconNode->nodeValue;
        }

        $titleNode = $this->xpath->query('/html/head/title')->item(0);
        $seoInfo['title'] = $titleNode ? $titleNode->nodeValue : false;

        // meta description
        $descriptionNode = $this->xpath->query('/html/head/meta[@name="description"]/@content')->item(0);
        $seoInfo['description'] = $descriptionNode ? $descriptionNode->nodeValue : false;

        // Extract meta keywords
        $keywordsNode = $this->xpath->query('/html/head/meta[@name="keywords"]/@content')->item(0);
        $seoInfo['metaKeywords'] = $keywordsNode ? $keywordsNode->nodeValue : false;

        // Headings
        $headings = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $seoInfo['headings'] = [];
        foreach ($headings as $heading) {
            $headingNodes = $this->xpath->query("//{$heading}");
            foreach ($headingNodes as $headingNode) {
                $text = trim($headingNode->textContent);
                $seoInfo['headings'][$heading][] = $text;
            }
        }

        // image extract 
        $starttime = microtime(true);
        $imageNodes = $this->xpath->query('//img[@src]');
        $seoInfo['totalImageCount'] = $imageNodes->length;
        $seoInfo['images'] = [];

        // Collect the image URLs to check for image formats
        $imageUrls = [];
        foreach ($imageNodes as $imageNode) {
            $imageSrc = $imageNode->getAttribute('src');
            $imageUrls[] = $imageSrc;
            $imageAlt = $imageNode->getAttribute('alt');
            $imageTitle = $imageNode->getAttribute('title');

            $imageInfo = [
                'src' => $imageSrc,
                'alt' => $imageAlt,
                'title' => $imageTitle,
            ];
            $seoInfo['images'][] = $imageInfo;
        }
        // Fetch image formats asynchronously using the helper function
        $imageFormats = checkImageFormats($imageUrls); // Wait for the promises to complete
        $seoInfo['notModernImage'] = $imageFormats;


        // execution time analyze
        $endtime = microtime(true);
        $executionTime = $endtime - $starttime;
        // echo "Execution time: " . $executionTime . " seconds\n" . PHP_EOL;
        // Add more code here to extract other SEO information as needed

        return $seoInfo;
    }
}

<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$url = $_GET['url'];

// Validate and sanitize the URL input
if (!filter_var($url, FILTER_VALIDATE_URL)) {
  echo json_encode(['error' => 'Invalid URL']);
  exit;
}

// Function to fetch the HTML content of a URL
function fetchHTML($url)
{
  $options = [
    'http' => [
      'method' => 'GET',
      'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36'
    ]
  ];
  $context = stream_context_create($options);
  $html = file_get_contents($url, false, $context);
  return $html;
}

// Function to check if a URL exists and returns a 200 status code
function urlExists($url)
{
  $headers = get_headers($url, 1);
  return (bool) ($headers && strpos($headers[0], '200') !== false);
}

// Function to check if a meta tag with a specific name and content exists
function hasMetaTag($html, $name, $content)
{
  $dom = new DOMDocument();
  libxml_use_internal_errors(true);
  $dom->loadHTML($html);
  libxml_use_internal_errors(false);

  $metaTags = $dom->getElementsByTagName('meta');
  foreach ($metaTags as $metaTag) {
    if ($metaTag->getAttribute('name') === $name && $metaTag->getAttribute('content') === $content) {
      return true;
    }
  }
  return false;
}

// Function to count the nodes in a DOM
function countNodes($node)
{
  $count = 0;

  if ($node->nodeType === XML_ELEMENT_NODE) {
    $count++;
  }

  if ($node->hasChildNodes()) {
    $children = $node->childNodes;
    foreach ($children as $child) {
      $count += countNodes($child);
    }
  }

  return $count;
}

// Fetch the HTML content of the provided URL
$html = fetchHTML($url);

// Check if the URL exists and returns a 200 status code
if (!urlExists($url)) {
  echo json_encode(['error' => 'URL not found or returned a non-200 status code']);
  exit;
}

// Check for Robots.txt file existence and a 200 status code
$robotsTxtUrl = rtrim($url, '/') . '/robots.txt';
$hasRobotsTxt = urlExists($robotsTxtUrl);

// Check if the nofollow and noindex meta tags exist
$hasNofollow = hasMetaTag($html, 'robots', 'nofollow');
$hasNoindex = hasMetaTag($html, 'robots', 'noindex');

// Check for URL redirects
$headers = get_headers($url, 1);
$redirects = isset($headers['Location']) ? $headers['Location'] : null;

// Calculate the page size in bytes
$pageSize = strlen($html);

// Create a DOMDocument object and load the HTML
$dom = new DOMDocument();
libxml_use_internal_errors(true); // Ignore any HTML parsing errors
$dom->loadHTML($html);
libxml_use_internal_errors(false);

// Create a DOMXPath object to query the DOM
$xpath = new DOMXPath($dom);

// Extract the language attribute of the HTML document
$language = $dom->documentElement->getAttribute('lang');

// Count the total number of nodes in the DOM
$totalNodes = countNodes($dom);

// Get the number of images in the DOM
$images = $xpath->query('//img');
$numImages = $images->length;

// Get the number of links in the DOM
$links = $xpath->query('//a');
$numLinks = $links->length;

// Get the number of headings (h1 to h6) in the DOM
$headings = [];
for ($i = 1; $i <= 6; $i++) {
  $headings[$i] = $xpath->query("//h$i")->length;
}

// Assemble the result data
$result = [
  'url' => $url,
  'exists' => true,
  'language' => $language,
  'page_size_bytes' => $pageSize,
  'num_nodes' => $totalNodes,
  'num_images' => $numImages,
  'num_links' => $numLinks,
  'num_headings' => $headings,
  'has_robots_txt' => $hasRobotsTxt,
  'has_nofollow_meta' => $hasNofollow,
  'has_noindex_meta' => $hasNoindex,
  'redirects' => $redirects,
];

// Send the JSON response
echo json_encode($result);


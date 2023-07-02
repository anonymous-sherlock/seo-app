<?php
header('Content-Type: application/json');

$url = $_GET['url'];

// Validate and sanitize the URL input
if (!filter_var($url, FILTER_VALIDATE_URL)) {
  echo json_encode(['error' => 'Invalid URL']);
  exit;
}

// Function to fetch the HTML content of a URL
function fetchHTML($url)
{
  $options = array(
    'http' => array(
      'method' => 'GET',
      'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36'
    )
  );
  $context = stream_context_create($options);
  $html = file_get_contents($url, false, $context);
  return $html;
}

// Function to check for URL redirects
function checkURLRedirects($url)
{
  $headers = get_headers($url, 1);

  if (isset($headers['Location'])) {
    if (is_array($headers['Location'])) {
      return $headers['Location'][count($headers['Location']) - 1];
    } else {
      return $headers['Location'];
    }
  }

  return null;
}
// Function to check if robots.txt exists
function checkRobotsTxt($url)
{
  $robotsTxtUrl = rtrim($url, '/') . '/robots.txt';
  $headers = get_headers($robotsTxtUrl);
  if ($headers && strpos($headers[0], '200') !== false) {
    return true; // robots.txt exists and returns a 200 status code
  }
  return false; // robots.txt does not exist or returns a non-200 status code
}
// Function to check if the nofollow meta tag exists
function hasNofollowTag($html)
{
  $dom = new DOMDocument();
  libxml_use_internal_errors(true);
  $dom->loadHTML($html);
  libxml_use_internal_errors(false);

  $metaTags = $dom->getElementsByTagName('meta');
  foreach ($metaTags as $metaTag) {
    if ($metaTag->getAttribute('name') === 'robots' && $metaTag->getAttribute('content') === 'nofollow') {
      return true; // nofollow meta tag exists
    }
  }
  return false; // nofollow meta tag does not exist
}
// Function to check if the noindex meta tag exists
function hasNoindexTag($html)
{
  $dom = new DOMDocument();
  libxml_use_internal_errors(true);
  $dom->loadHTML($html);
  libxml_use_internal_errors(false);

  $metaTags = $dom->getElementsByTagName('meta');
  foreach ($metaTags as $metaTag) {
    if ($metaTag->getAttribute('name') === 'robots' && $metaTag->getAttribute('content') === 'noindex') {
      return true; // noindex meta tag exists
    }
  }
  return false; // noindex meta tag does not exist
}
// Fetch the HTML content of the provided URL
$html = fetchHTML($url);
// Check if the Robots.txt nofollow, noindex.
$hasRobotsTxt = checkRobotsTxt($url);
$hasNofollow = hasNofollowTag($html);
$hasNoindex = hasNoindexTag($html);
// Check for URL redirects
$redirects = checkURLRedirects($url);
// Calculate the page size in bytes
$pageSize = strlen($html);

// Create a DOMDocument object and load the HTML
$dom = new DOMDocument();
libxml_use_internal_errors(true); // Ignore any HTML parsing errors
$dom->loadHTML($html);
libxml_use_internal_errors(false);

// Create a DOMXPath object to query the DOM
$xpath = new DOMXPath($dom);

// language
$language = $dom->documentElement->getAttribute('lang');
// Extract the title, favicon, headings, description, images, language
$titleNode = $xpath->query('//title')->item(0);
$title = $titleNode ? $titleNode->textContent : '';
// favicon
$faviconNode = $xpath->query('//link[@rel="icon" or @rel="shortcut icon"]/@href')->item(0);
$favicon = $faviconNode ? $faviconNode->textContent : '';
// heading
$headings = ['h1' => '', 'h2' => '', 'h3' => '', 'h4' => ''];
foreach ($headings as $heading => &$value) {
  $headingNode = $xpath->query("//{$heading}")->item(0);
  $value = $headingNode ? $headingNode->textContent : '';
}
// meta description
$descriptionNode = $xpath->query('//meta[@name="description"]/@content')->item(0);
$description = $descriptionNode ? $descriptionNode->textContent : '';


// Calculate the DOM size Function to count the nodes in the DOM
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
// Calculate the DOM size (number of nodes)
$domSize = countNodes($dom->documentElement);
// Checking for Doctype 
$hasDoctype = strpos($html, '<!DOCTYPE html>') !== false;
// Extract the server signature from the response headers
$headers = get_headers($url, 1);
$serverSignature = isset($headers['Server']) ? $headers['Server'] : null;

// new code add here

// Extract images without alt attribute text and total images used in website
$totalImageCount = 0;
$imagesWithoutAltText = [];
$imageNodes = $xpath->query('//img[not(@alt) or @alt=""]');
foreach ($imageNodes as $imageNode) {
  $src = $imageNode->getAttribute('src');
  if (!empty($src)) {
    $imagesWithoutAltText[] = $src;

  }
  // Increment totalImageCount only if src is not blank
  $totalImageCount++;
}







// new code end

// Build the SEO report array
$report = [
  'url' => $url,
  'title' => $title,
  'favicon' => $favicon,
  'headings' => $headings,
  'description' => $description,
  'language' => $language,
  'hasNofollow' => $hasNofollow,
  'hasNoindex' => $hasNoindex,
  'hasRobotsTxt' => $hasRobotsTxt,
  'redirects' => $redirects,
  'pageSize' => $pageSize,
  'domSize' => $domSize,
  'hasDoctype' => $hasDoctype,
  'serverSignature' => $serverSignature,
  'imagesWithoutAlt' => $imagesWithoutAltText,
  'totalImageCount' => $totalImageCount
];

// Send the report as JSON response
echo json_encode($report);
?>
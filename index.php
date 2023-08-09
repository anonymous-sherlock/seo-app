<?php
require_once __DIR__ . '/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);



if (isset($_POST['url'])) {
    $url = $_POST['url'];
} elseif (isset($_GET['url'])) {
    $url = $_GET['url'];
} else {
    // URL not provided, return an error response
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Bad Request: URL not provided']);
    exit;
}

// Perform URL decoding for the provided URL
$url = urldecode($url);

// Validate the URL using filter_var function
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    // Invalid URL provided, return an error response
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Bad Request: Invalid URL provided']);
    exit;
}

// Now, create an instance of the WebsiteSEOChecker class and proceed with the analysis
$seoChecker = new WebsiteSEOChecker();
$seoInfo = $seoChecker->checkSEO($url);

// Output the SEO information as JSON
header('Content-Type: application/json');
echo json_encode($seoInfo);

<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);


try {
    // Get the URL from the query parameter
    $url = $_GET['url'];


    $seoChecker = new WebsiteSEOChecker();
    $seoInfo = $seoChecker->checkSEO($url);



    // Output the SEO information as JSON
    header('Content-Type: application/json');
    echo json_encode($seoInfo);
} catch (Exception $e) {
    // Log the error and return an error response
    error_log('Error processing website SEO: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

<?php

require 'lib/html-dom-lib/simple_html_dom.php'; // Include the lightweight HTML parser library

function performSEOAnalysis($url)
{
  $seoData = array();

  // Check if the cached analysis result is available
  $cacheFile = 'seo_cache/' . md5($url) . '.json';
  $cacheExpiry = 3600; // Cache expiry time in seconds (1 hour)

  if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
    // Retrieve the analysis result from the cache
    $seoData = json_decode(file_get_contents($cacheFile), true);
  } else {
    // Fetch the HTML content of the URL
    $html = file_get_html($url);

    if ($html !== false) {
      // Extract title tag
      $titleTag = $html->find('title', 0);
      if ($titleTag !== null) {
        $seoData['title'] = $titleTag->plaintext;
      }

      // Extract h1 tags
      $h1Tags = $html->find('h1');
      if (!empty($h1Tags)) {
        $h1Array = array();
        foreach ($h1Tags as $h1) {
          $h1Array[] = $h1->plaintext;
        }
        $seoData['h1'] = $h1Array;
      }

      // Extract h2 tags
      $h2Tags = $html->find('h2');
      if (!empty($h2Tags)) {
        $h2Array = array();
        foreach ($h2Tags as $h2) {
          $h2Array[] = $h2->plaintext;
        }
        $seoData['h2'] = $h2Array;
      }

      // Extract meta description
      $metaDescriptionTag = $html->find('meta[name=description]', 0);
      if ($metaDescriptionTag !== null) {
        $seoData['description'] = $metaDescriptionTag->content;
      }

      // Extract Open Graph (og) tags
      $ogTags = $html->find('meta[property^=og:]');
      if (!empty($ogTags)) {
        $ogArray = array();
        foreach ($ogTags as $og) {
          $ogArray[$og->property] = $og->content;
        }
        $seoData['og_tags'] = $ogArray;
      }

      // Check alt attributes of images
      $images = $html->find('img');
      $allImagesHaveAlt = true;

      foreach ($images as $image) {
        if (!isset($image->alt) || empty($image->alt)) {
          $allImagesHaveAlt = false;
          break;
        }
      }

      $seoData['all_images_have_alt'] = $allImagesHaveAlt;

      // Check broken links
      $brokenLinks = array();

      foreach ($html->find('a') as $link) {
        $href = $link->href;

        // Skip empty or non-http(s) URLs
        if (empty($href) || !preg_match('/^https?:\/\//i', $href)) {
          continue;
        }

        // Check if the URL is valid
        $headers = @get_headers($href);

        if ($headers && strpos($headers[0], '200') === false) {
          $brokenLinks[] = $href;
        }
      }

      $seoData['broken_links'] = $brokenLinks;

      // Cache the analysis result
      file_put_contents($cacheFile, json_encode($seoData));
    }

    // Clean up the HTML parser
    $html->clear();
    unset($html);
  }

  return $seoData;
}

// Get the URL parameter from the query string
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Perform SEO analysis for the provided URL
$seoAnalysis = performSEOAnalysis($url);

// Convert the SEO analysis data to JSON format
$jsonData = json_encode($seoAnalysis);

// Set the response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Send the JSON response
echo $jsonData;
<?php

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Request;

function is404Page($client, $url)
{
  $nonExistentPageUrl = rtrim($url, '/') . '/non-existent-page';

  $promises = [
    'response' => $client->headAsync($nonExistentPageUrl, [
      'http_errors' => false,
      'timeout' => 5, // Set a timeout of 5 seconds
    ]),
  ];

  $results = Utils::unwrap($promises);

  $response = $results['response'];
  $statusCode = $response->getStatusCode();

  if ($statusCode === 404) {
    return true; // Custom 404 page exists
  }
  return false; // No custom 404 page
}

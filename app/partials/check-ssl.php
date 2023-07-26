<?php
function getSSLCertificateInfo($hostname)
{
    $context = stream_context_create([
        'ssl' => [
            'capture_peer_cert' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);

    // Attempt to establish an SSL/TLS connection
    $stream = @stream_socket_client("ssl://$hostname:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if (!$stream) {
        return []; // Return an empty array if the connection fails
    }

    // Retrieve the peer certificate
    $params = stream_context_get_params($stream);
    $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

    // Close the SSL/TLS connection
    fclose($stream);

    if (!$cert) {
        return []; // Return an empty array if the certificate cannot be parsed
    }

    // Extract the validity status, issuer, creation date, and expiration date
    $isValid = time() <= $cert['validTo_time_t'];
    $issuer = $cert['issuer']['O'] ?? false;
    $createdAt = date('Y-m-d H:i:s', $cert['validFrom_time_t']);
    $expireAt = date('Y-m-d H:i:s', $cert['validTo_time_t']);

    // Return the SSL certificate information
    return [
        'is_valid' => $isValid,
        'issuer' => $issuer,
        'created_at' => $createdAt,
        'expire_at' => $expireAt
    ];
}

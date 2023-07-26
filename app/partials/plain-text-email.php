<?php


function plainTextEmail(DOMXPath $xpath)
{
    $plainEmails = [];
    // Find email addresses using a regular expression
    // Use XPath to find email addresses
    $emailNodes = $xpath->query('//text()[contains(., "@")]');

    // Extract and filter valid email addresses
    foreach ($emailNodes as $emailNode) {
        $email = trim($emailNode->nodeValue);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $plainEmails[] = $email;
        }
    }
    return $plainEmails;
}


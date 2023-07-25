<?php
function getSPFRecord($domain)
{
    $spfRecords = @dns_get_record($domain, DNS_TXT);

    foreach ($spfRecords as $record) {
        if (stripos($record['txt'], 'v=spf1') !== false) {
            return $record['txt'];
        }
    }

    return $spfRecords[0]['txt'] ?? false;
}

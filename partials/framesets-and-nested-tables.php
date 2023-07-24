<?php

function nestedTablesTest(DOMXPath $xpath)
{
    $nestedTables = $xpath->query('//table//table');
    return $nestedTables->length > 0 ? $nestedTables->length : false;
}

function framesetsTest(DOMXPath $xpath)
{
    $framesets = $xpath->query('/descendant::frameset');
    return $framesets->length > 0 ? $framesets->length : false;
}

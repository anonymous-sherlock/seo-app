<?

require_once __DIR__ . '/registery.php';
function isInternal($url)
{
    $domain = Registery::getDomain();
    echo $domain;
    if (strpos($url, $domain) > 0) {
        return true;
    }
    return false;
}

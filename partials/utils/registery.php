<?php
// Registery.php

class Registery
{

    private static $domain;
    public static function setDomain($domain)
    {
        self::$domain = $domain;
    }
    public static function getDomain()
    {
        return self::$domain;
    }
}

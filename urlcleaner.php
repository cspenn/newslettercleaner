<?php

/***********************************************

Bitly newsletter cleaner
Copyright © 2016 Christopher S. Penn

The purpose of this code is to take a list of shortened bit.ly or other redirected URLs, lengthen them, chop off any existing UTM codes and other query parameters to produce a clean URL, then append new UTM codes to it and re-shorten with bit.ly. The output is a CSV, pipe-delimited.

Requirements:

cUrl for PHP/CLI
A bit.ly API key 
Read/write access to the local disk

************************************************/

// configuration stuff

date_default_timezone_set('America/New_York');
ini_set('auto_detect_line_endings', TRUE);

$apikey = "INSERT YOUR BITLY API KEY HERE";

// logfile outputs as a CSV file with a pipe delimiter

$stamp      = date("Y-m-d-h-i-s");
$shortstamp = date("Y-m-d");
$logfile    = "newsletteroutput-$stamp.csv";
$fp         = fopen($logfile, "w");
fwrite($fp, "URL|count\n");

// What are your UTM codes? These are source, medium, and campaign. Avoid using spaces.

$source   = "";
$medium   = "";
$campaign = "" . $shortstamp; // shortstamp appends a yyyy-mm-dd timestamp

// input file to open

$handle = @fopen("input.txt", "r");

// parse the file, line by line

if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        $url = trim($buffer);
        
        // expand raw bitly links
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE); // We'll parse redirect url from header.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE); // We want to just get redirect url but not to follow it.
        $response = curl_exec($ch);
        preg_match_all('/^Location:(.*)$/mi', $response, $matches);
        curl_close($ch);
        $longurl = !empty($matches[1]) ? trim($matches[1][0]) : 'No redirect found';
        
        // split and clean 
        
        list($shorturl, $string) = explode('?', $longurl, 2);
        
        // append UTM codes      
        
        $utms = "?utm_source=" . $source . "&utm_medium=" . $medium . "&utm_campaign=" . $campaign;
        
        $longurl = $shorturl . $utms;
        
        // re-encode
        $bitly = "https://api-ssl.bitly.com/v3/shorten?&access_token=$apikey&longUrl=$longurl";
        
        $results = json_decode(file_get_contents($bitly), true);
        $theurl  = $results['data']['url'];
        
        fwrite($fp, "$url|$longurl|$theurl\n");
        
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}


?>

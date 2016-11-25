<?php

/***********************************************

Bitly newsletter cleaner
Copyright © 2016-2017 Christopher S. Penn

The purpose of this code is to take a list of shortened bit.ly or other redirected URLs, lengthen them, chop off any existing UTM codes and other query parameters to produce a clean URL, then append new UTM codes to it and re-shorten with bit.ly. The output is a CSV, pipe-delimited.

Version 2.0

What's New:
- Now creates markdown output instead of CSV
- Fails gracefully for the most part on crap URLs
- Deals with non-shortened URLs better

Requirements:

cUrl for PHP/CLI
A bit.ly API key 
Read/write access to the local disk

************************************************/

// configuration stuff

date_default_timezone_set('America/New_York');
ini_set('auto_detect_line_endings', TRUE);

$apikey = "PUT YOUR BITLY API KEY HERE";

// logfile outputs as a CSV file with a pipe delimiter

$stamp      = date("Y-m-d-h-i-s");
$shortstamp = date("Y-m-d");
$logfile    = "output-$stamp.csv";
$fp         = fopen($logfile, "w");
//fwrite($fp, "URL|count\n");

// What are your UTM codes? These are source, medium, and campaign. Avoid using spaces.

$source   = "YOUR SOURCE";
$medium   = "YOUR MEDIUM";
$campaign = "YOUR CAMPAIGN" . $shortstamp; // shortstamp appends a yyyy-mm-dd timestamp

// Page Title Getter Function

function file_get_contents_curl($url)
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}

// input file to open

$handle = @fopen("YOUR INPUT FILE HERE.txt", "r");

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
        
        $longurl = !empty($matches[1]) ? trim($matches[1][0]) : $longurl = $url;
        
        // split and clean 
        
        list($shorturl, $string) = explode('?', $longurl, 2);
        
        // append UTM codes      
        
        $utms = "?utm_source=" . $source . "&utm_medium=" . $medium . "&utm_campaign=" . $campaign;
        
        $longurl = $shorturl . $utms;
        
        // obtain page title
        
        $html = file_get_contents_curl($shorturl);
        
        //parsing begins here:
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $nodes = $doc->getElementsByTagName('title');
        
        //get and display what you need:
        $title = $nodes->item(0)->nodeValue;
        
        $metas = $doc->getElementsByTagName('meta');
        
        for ($i = 0; $i < $metas->length; $i++) {
            $meta = $metas->item($i);
            if ($meta->getAttribute('name') == 'description')
                $description = $meta->getAttribute('content');
            if ($meta->getAttribute('name') == 'keywords')
                $keywords = $meta->getAttribute('content');
        }
        
        $pagetitle = trim($title);
        
        // re-encode
        $bitly = "https://api-ssl.bitly.com/v3/shorten?&access_token=$apikey&longUrl=$longurl";
        
        $results = json_decode(file_get_contents($bitly), true);
        $theurl  = $results['data']['url'];
        
        // old format
        // fwrite($fp, "$url\t$longurl\t$theurl\t$pagetitle\n");
        
        // new format for markdown
        fwrite($fp, "[$pagetitle]($theurl) : $description\n");
        
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}


?>

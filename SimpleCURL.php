<?php

/**
 * Simple class for CURLing a website
 * NOTE: not thread safe!!!
 */

class CantLoadHTML extends Exception {}

class SimpleCURL
{
    public static $lastResult = null;

    const ATTEMPTS = 10;
    private static $curl = null;
    private static $headers = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT => 200,
        CURLOPT_HEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.8,lv;q=0.6',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
        ]
    ];

    public static function getSite(string $url): CurlResult
    {
        if(SimpleCURL::$curl === null) {
            libxml_use_internal_errors(true);
            SimpleCURL::$curl = curl_init();
        }

        SimpleCURL::$headers[CURLOPT_URL] = $url;
        curl_setopt_array(SimpleCURL::$curl, SimpleCURL::$headers);

        do {
            static $attempts = 0;
            $result = curl_exec(SimpleCURL::$curl);
            $dom = new DOMDocument();
            try {
                $dom->loadHTML($result);
                $attempts++;
            } catch (Exception $e) {
                $dom = NULL;
            }
        } while ($dom == NULL && $attempts < SimpleCURL::ATTEMPTS);

        if ($dom != NULL) {
            $xpath = new DOMXPath($dom);
        } else {
            throw new CantLoadHTML();
        }

        $responseCode = curl_getinfo(SimpleCURL::$curl, CURLINFO_HTTP_CODE);
        return new CurlResult($result, $responseCode, $xpath, $url);
    }
}

class CurlResult
{
    /** @var  $dom DOMXPath */
    public $xpath;
    public $html;
    public $responseCode;
    public $url;

    public function __construct($html, $responseCode, DOMXPath $DOMXPath, $url)
    {
        $this->html = $html;
        $this->xpath = $DOMXPath;
        $this->responseCode = $responseCode;
        $this->url = $url;
    }
}


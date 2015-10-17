<?php

namespace SiteParser\Network;

class Url implements UrlInterface
{
    protected $urlString;
    protected $scheme;
    protected $host;
    protected $path;

    public function __construct($url)
    {
        $this->urlString = $url;
        $this->parseUrl();
    }

    protected function parseUrl()
    {
        $this->scheme = parse_url($this->urlString, PHP_URL_SCHEME);
        $this->host = parse_url($this->urlString, PHP_URL_HOST);
        $this->port = parse_url($this->urlString, PHP_URL_PORT);
        $this->path = parse_url($this->urlString, PHP_URL_PATH);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path;
    }
}

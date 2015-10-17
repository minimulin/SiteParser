<?php

namespace SiteParser\Network;

interface HttpRequestInterface
{
    public function __construct(UrlInterface $url, $encoding);

    public function getContent();
    public function createConnection();
}

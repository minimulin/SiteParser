<?php

namespace SiteParser\Network;

interface UrlInterface
{
    public function __construct($url);

    public function getHost();
    public function getScheme();
    public function getPort();
    public function getPath();
}

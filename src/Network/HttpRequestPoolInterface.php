<?php

namespace SiteParser\Network;

interface HttpRequestPoolInterface
{
	public function __construct(array $urls, $concurrentRequests);
	public function start();
}

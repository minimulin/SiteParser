<?php

namespace SiteParser\Network;

use Closure;
use Exception;

class HttpRequestPool implements HttpRequestPoolInterface
{
    protected $urls;
    protected $concurrentRequests;
    protected $activeRequests;

    /**
     * [__construct description]
     * @param Url[] $urls Массив объектов Url
     */
    public function __construct(array $urls, $concurrentRequests)
    {
        $this->urls = $urls;
        $this->concurrentRequests = $concurrentRequests;
        $this->activeRequests = [];
    }

    public function start(Closure $onConnectionClose = null)
    {
        $result = [];
        while (count($this->urls) || count($this->activeRequests)) {
            if (count($this->activeRequests) < $this->concurrentRequests && count($this->urls)) {
                $url = current($this->urls);
                $this->activeRequests[key($this->urls)] = (new HttpRequest($url))->createConnection();
                $result[key($this->urls)] = '';
                unset($this->urls[key($this->urls)]);
                count($this->urls);
            }

            $read = $this->activeRequests;
            stream_select($read, $w = null, $e = null, 100);
            if (count($read)) {
                foreach ($read as $r) {
                    $id = array_search($r, $this->activeRequests);

                    $data = fread($r, 1024);
                    if (strlen($data) == 0) {
                        if ($onConnectionClose) {
                            $onConnectionClose($id,$result[$id]);
                        }                        

                        fclose($r);
                        unset($this->activeRequests[$id]);
                    } else {
                        $result[$id] .= $data;
                    }
                }
            } else {
                throw new Exception('Time-out', 1);

                break;
            }
        }

        return $result;
    }

}

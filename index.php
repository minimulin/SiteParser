<?php

$loader = require_once __DIR__.'/vendor/autoload.php';

use SiteParser\Network\HttpRequest;

$httpRequest = new HttpRequest();

$content = $httpRequest->getContent('http://www.ruan.ru/address/6x3/spb/0/');

die(var_dump($content));






die();

	echo "Program starts at ". date('h:i:s') . ".\n";

        $timeout=10; 
        $result=array(); 
        $sockets=array(); 
        $convenient_read_block=8192;
        
        /* Выполнить одновременно все запросы; ничего не блокируется. */
        $delay=15;
        $id=0;
        while ($delay > 0) {
            $s=stream_socket_client("phaseit.net:80", $errno,
                  $errstr, $timeout,
                  STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT); 
            if ($s) { 
                $sockets[$id++]=$s; 
                $http_message="GET /demonstration/delay?delay=" .
                    $delay . " HTTP/1.0\r\nHost: phaseit.net\r\n\r\n"; 
                fwrite($s, $http_message);
            } else { 
                echo "Stream " . $id . " failed to open correctly.";
            } 
            $delay -= 3;
        } 
        
        while (count($sockets)) { 
            $read=$sockets; 
            stream_select($read, $w=null, $e=null, $timeout); 
            if (count($read)) {
                /* stream_select обычно перемешивает $read, поэтому мы должны вычислить, 
                   из какого сокета выполняется чтение.  */
                foreach ($read as $r) { 
                    $id=array_search($r, $sockets); 
                    $data=fread($r, $convenient_read_block); 
                    /* Сокет можно прочитать либо потому что он
                       имеет данные для чтения, ЛИБО потому что он в состоянии EOF. */
                    if (strlen($data) == 0) { 
                        echo "Stream " . $id . " closes at " . date('h:i:s') . ".\n";
                        fclose($r); 
                        unset($sockets[$id]); 
                    } else { 
                        $result[$id] .= $data; 
                    } 
                } 
            } else { 
                /* Таймаут означает, что *все* потоки не
                   дождались получения ответа. */
                echo "Time-out!\n";
                break;
            } 
        } 
       ?>
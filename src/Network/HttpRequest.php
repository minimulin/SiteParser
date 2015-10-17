<?php

namespace SiteParser\Network;

class HttpRequest implements HttpRequestInterface
{
    const PORT = '80';

    const UTF8 = 'utf-8';
    const CP1251 = 'windows-1251';

    protected $headers;
    protected $content;
    protected $socket;


    public function __construct(UrlInterface $url, $encoding = HttpRequest::UTF8)
    {
        $this->url = $url;
        $this->headers = '';
        //todo: Реализовать декодирование из одной кодировки в другую. Только для текстовых mime-типов
        $this->endcoding = $encoding;
    }

    /**
     * Открывает соединение с сокетом
     */
    protected function openConnection()
    {
        $this->socket = stream_socket_client($this->url->getHost() . ':' . ($this->url->getPort() ? $this->url->getPort() : static::PORT), $errno,
            $errstr, 10,
            STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT);

        if ($this->socket) {
            $headers = $this->buildHeaders();
            fwrite($this->socket, $headers);
        } else {
            throw new Exception('Stream failed to open correctly.', 1);
        }
    }

    /**
     * Закрывает соединение
     */
    protected function closeConnection()
    {
        fclose($this->socket);
    }

    /**
     * Возвращает текст запрашиваемого документа
     * @return string Текст документа
     */
    public function getContent()
    {
        $result = '';

        $this->openConnection();

        while ($data = fread($this->socket, 1024)) {
            $result .= $data;
        }

        $this->closeConnection();

        return $result;
    }

    /**
     * Подготавливает и возвращает заголовки перед запросом
     * @return string Заголовки
     */
    protected function buildHeaders()
    {
        $this->addHeader('GET ' . $this->url->getPath() . ' HTTP/1.0');
        $this->addHeader('Host: ' . $this->url->getHost());
        $this->addHeader('Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7');

        $this->addHeader('');
        return $this->headers;
    }

    /**
     * Осуществляет добавление заголовка
     */
    protected function addHeader($header)
    {
        $this->headers .= $header . "\r\n";
    }
}

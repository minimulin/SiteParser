<?php

namespace SiteParser\Network;

use Closure;
use Exception;
use SiteParser\Exceptions\TimeoutException;

/**
 * Класс, реализующий параллельную обработку получения результатов запросов
 */
class HttpRequestPool implements HttpRequestPoolInterface
{
    const READ_BLOCK_LENGTH = 1024;
    const TIMEOUT = 100;

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

    /**
     * Обрабатывает параллельное получение результатов от массива запросов
     * @param  Closure|null $onConnectionClose Анонимная функция, которая вызывается при получении ответа на запрос.
     * @return array                           Массив ответов на запрос
     */
    public function start(Closure $onConnectionClose = null)
    {
        $result = [];
        //Продолжаем до тех пор, пока остались не обработанные запросы
        while (count($this->urls) || count($this->activeRequests)) {
            //Держим не более $this->concurrentRequests активных запросов
            if (count($this->activeRequests) < $this->concurrentRequests && count($this->urls)) {
                $url = current($this->urls);
                //Создаём соединение для Url
                $this->activeRequests[key($this->urls)] = (new HttpRequest($url))->createConnection();
                $result[key($this->urls)] = '';
                unset($this->urls[key($this->urls)]);
            }

            $read = $this->activeRequests;
            //Получаем массив с потоками, которые могут что-либо вернуть
            stream_select($read, $w = null, $e = null, static::TIMEOUT);
            if (count($read)) {
                foreach ($read as $r) {
                    $id = array_search($r, $this->activeRequests);

                    $data = fread($r, static::READ_BLOCK_LENGTH);
                    //Если данные из потока считаны до конца
                    if (strlen($data) == 0) {
                        //Вызываем анонимную функцию, если она задана
                        if ($onConnectionClose) {
                            $onConnectionClose($id, $result[$id]);
                        }

                        //Закрываем и удаляем поток из активных
                        fclose($r);
                        unset($this->activeRequests[$id]);
                    } else {
                        $result[$id] .= $data;
                    }
                }
            } else {
                //Если все потоки превысили интервал ожидания
                throw new TimeoutException('Time-out', 1);

                break;
            }
        }

        return $result;
    }

}

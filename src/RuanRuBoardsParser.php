<?php

namespace SiteParser;

use SiteParser\Exceptions\ParseException;
use SiteParser\Network\HttpRequest;
use SiteParser\Network\HttpRequestPool;
use SiteParser\Network\Url;
use SiteParser\Utils\Echoer;
use SiteParser\Utils\HtmlParser;

/**
 *
 */
class RuanRuBoardsParser
{
    public function getBoards()
    {

        Echoer::info('Запуск');

        $root = 'http://www.ruan.ru';

        $httpRequest = new HttpRequest(new Url($root . '/address/6x3/spb/0/'), HttpRequest::CP1251);
        $content = iconv('windows-1251', 'utf-8', $httpRequest->getContent());

        Echoer::info('Код страницы получен');

        try {
            //Получаем form с именем 'sel'
            $form = HtmlParser::getTagContentWithAttributeValue($content, 'form', 'name', 'sel');
            //Извлекаем из формы таблицу
            $table = HtmlParser::getTagContent($form, 'table');
            //Получаем массив строк таблицы
            $trs = HtmlParser::getTagsArray($form, 'tr');

        } catch (ParseException $e) {
            Echoer::error('Структура страницы не соответствует алгоритму парсинга. Аварийное завершение скрипта');
            die(1);
        }

        $boards = [];
        $links = [];

        foreach ($trs as $key => $tr) {
            //Получаем массив всех элементов в строке таблицы
            $tds = HtmlParser::parseAllTags($tr);
            //Шапку пропускаем
            if ($key > 0) {
                //Сторона
                $sideRu = mb_substr($tds[1][3], 0, 1);
                switch (mb_strtolower($sideRu)) {
                    case 'а':
                        $side = 'A';
                        break;
                    case 'б':
                        $side = 'B';
                        break;

                    default:
                        $side = 'C';
                        break;
                }

                //Адрес
                $address = HtmlParser::getTagContent($tds[2][3], 'a');

                //Получаем содержимое первого столбца для каждой строчки таблицы
                $value = HtmlParser::getTagAttribute($tds[0][3], 'value');

                //Получаем содержимое третьего столбца для каждой строчки таблицы
                $id = HtmlParser::getTagAttribute($tds[2][0], 'id');

                $link = "$root/address/html/6x3/$id/$value/";

                $board = [
                    'bb_address' => $address,
                    'bb_side' => $side,
                    'bb_link_owner_site' => $link,
                    //Изначальный вариант получания картинок. Не предусматривает обработку отсутствующих. Ниже будет реализация с учётом отсутствия изображений
                    'bb_image' => "$root/img/timed/photo/{$value}a.jpg",
                    'bb_shema' => "$root/img/timed/map/$value.gif",
                ];

                $links[$id] = new Url($link);
                $boards[$id] = $board;
            }

        }

        Echoer::info('Извлечено ' . count($boards) . ' записей');

        Echoer::info('Идёт получение и парсинг изображений и схем (1* = 10 записей)');
        //Класс для распараллеливания запросов
        $pool = new HttpRequestPool($links, 4);

        try {
            //Для экономии времени сразу по получении ответа парсим его. Реализовано через анонимную функцию
            $detailedPagesContent = $pool->start(function ($id, $content) use ($boards, $root) {
                //Каждые 10 полученных ответов выводим звёздочку
                if ($id > 0 && $id % 10 == 0) {
                    echo '*';
                }

                $tds = HtmlParser::getTagsArray($content, 'td');
                //Извлекаем фотографию поверхности
                if (isset($tds[0])) {
                    $image = HtmlParser::getTagAttribute($tds[0], 'src');
                    //Местами можно встретить подобный URL вместо отсутствующего изображения
                    if ($image == '/img/timed/photo/no.gif') {
                        $image = null;
                    }
                } else {
                    $image = null;
                }

                //Извлекаем схему
                if (isset($tds[1])) {
                    $scheme = HtmlParser::getTagAttribute($tds[1], 'src');
                    if ($scheme == '/img/timed/photo/no.gif') {
                        $scheme = null;
                    }
                } else {
                    $scheme = null;
                }

                //Хочу заметить, что проверка на существование изображений не осуществляется.
                $boards[$id]['bb_image'] = $image ? ($root . $image) : null;
                $boards[$id]['bb_shema'] = $scheme ? ($root . $scheme) : null;
            });
        } catch (Exception $e) {
            Echoer::error('Возникла ошибка в процессе получения изображений и схем. Аварийное завершение скрипта');
            die(1);
        }

        Echoer::line();
        Echoer::info('Получено и обработано ' . count($detailedPagesContent) . ' пар изображений и схем');
        Echoer::success('Парсинг данных закончен');

        return $boards;
    }
}

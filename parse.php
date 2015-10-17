#!/usr/bin/php
<?php

/**
 * Пример CLI-скрипта на основе реализованных классов
 * По задумке для каждого сайта пишется свой CLI-скрипт
 * За счёт использования интерфейсов мы добиваемся меньшей свзности. Классы знают друг о друге ровно столько, сколько им положено для минимальной работы
 * Каждый класс выполняет лишь свою небольшую функцию
 * При разработке я старался следовать принципу SOLID
 */

$loader = require_once __DIR__ . '/vendor/autoload.php';

use SiteParser\Network\HttpRequest;
use SiteParser\Network\Url;
use SiteParser\HtmlParserUtils;
use SiteParser\Utils\Echoer;
use SiteParser\Exceptions\ParseException;

Echoer::info('Запуск');

$httpRequest = new HttpRequest(new Url('http://www.ruan.ru/address/6x3/spb/0/'), HttpRequest::CP1251);
$content = iconv('windows-1251', 'utf-8', $httpRequest->getContent());

Echoer::info('Код страницы получен');

try {
	//Получаем form с именем 'sel'
	$form = HtmlParserUtils::getTagContentWithAttributeValue($content, 'form', 'name', 'sel');	
	//Извлекаем из формы таблицу
	$table = HtmlParserUtils::getTagContent($form, 'table');
	//Получаем массив строк таблицы
	$trs = HtmlParserUtils::getTagsArray($form, 'tr');

} catch (ParseException $e) {
	Echoer::error('Структура страницы не соответствует алгоритму парсинга. Аварийное завершение скрипта');
	die(1);
}

$boards = [];

foreach ($trs as $key => $tr) {
	//Получаем массив всех элементов в строке таблицы
    $tds = HtmlParserUtils::parseAllTags($tr);
    //Шапку пропускаем
    if ($key > 0) {
    	//Сторона
        $side = mb_substr($tds[1][3], 0, 1);
        //Адрес
        $address = HtmlParserUtils::getTagContent($tds[2][3], 'a');

        //Для лучшей читаемости кода выделим все мелочи в отдельные переменные
        //Получаем содержимое первого столбца для каждой строчки таблицы
        $firstTdContent = $tds[0][3];
        $firstTdAttrs = HtmlParserUtils::getTagAttributes($firstTdContent);
        //$firstTdAttrs[1] содержит имена всех атрибутов
        //$firstTdAttrs[2] содержит значения всех атрибутов
        $value = filter_var($firstTdAttrs[2][array_search('value', $firstTdAttrs[1])], FILTER_SANITIZE_NUMBER_INT);

        //Получаем содержимое третьего столбца для каждой строчки таблицы
        $thirdTdContent = $tds[2][0];
        $thirdTdAttrs = HtmlParserUtils::getTagAttributes($thirdTdContent);
        $id = filter_var($thirdTdAttrs[2][array_search('id', $thirdTdAttrs[1])], FILTER_SANITIZE_NUMBER_INT);

        $link = "http://www.ruan.ru/address/html/6x3/$id/$value/";

        $board = [
            'bb_address' => $address,
            'bb_side' => $side,
            'bb_link_owner_site' => $link,
            //Изначальный вариант получания картинок. Не предусматривает обработку отсутствующих. Ниже будет реализация с учётом отсутствия изображений
            'bb_image' => "http://www.ruan.ru/img/timed/photo/{$value}a.jpg",
            'bb_shema' => "http://www.ruan.ru/img/timed/map/$value.gif",
        ];

        $boards[$id] = $board;
    }

}

Echoer::info('Извлечено ' . count($boards) . ' записей');
Echoer::success('Парсинг данных закончен');

return $boards;
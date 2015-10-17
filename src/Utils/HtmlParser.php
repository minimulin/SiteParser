<?php

namespace SiteParser\Utils;

use SiteParser\Exceptions\ParseException;

class HtmlParser
{
    /**
     * Возвращает массив всех найденных в документе тегов
     * array(5) {
     *  [0]=> string "<th><input type="checkbox" title="Отметить все"></th>"
     *  [1]=> string "<th>"
     *  [2]=> string "th"
     *  [3]=> string "<input type="checkbox" title="Отметить все">"
     *  [4]=> string "</th>"
     * }
     *
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    public static function parseAllTags($content)
    {
        preg_match_all("/(<([\w]+)[^>]*>)(.*?)(<\/\\2>)/", $content, $matches, PREG_SET_ORDER);
        return $matches;
    }

    /**
     * Возращает содержимое элемента по его тегу и значению атрибута
     * @param  string $string         Строка для поиска
     * @param  string $tag            Имя искомого тега
     * @param  string $attribute      Имя искомого атрибута
     * @param  string $attributeValue Искомое значение атрибута
     * @throws ParseException           Ошибка парсинга
     * @return string                   Содержимое элемента
     */
    public static function getTagContentWithAttributeValue($string, $tag, $attribute, $attributeValue)
    {
        $pattern = "/<{$tag}[^>]*$attribute=[\"']?{$attributeValue}[\"']?[^>]*>(.*?)<\/$tag>/is";
        preg_match_all($pattern, $string, $matches);
        if (!isset($matches[1][0])) {
            throw new ParseException("Не удаётся найти элемент $tag", 1);

        }
        return $matches[1][0];
    }

    /**
     * Возращает содержимое элемента по его тегу
     * @param  string $string         Строка для поиска
     * @param  string $tag            Имя искомого тега
     * @throws ParseException           Ошибка парсинга
     * @return string                   Содержимое элемента
     */
    public static function getTagContent($string, $tag)
    {
        $pattern = "/<$tag\b[^>]*>(.*?)<\/$tag>/is";
        preg_match_all($pattern, $string, $matches);
        if (!isset($matches[1][0])) {
            throw new ParseException("Не удаётся найти элемент $tag", 1);

        }
        return $matches[1][0];
    }

    /**
     * Возвращает атрибуты элемента, переданного в строке
     * @param  string $string Строка с элементом
     * @return array          Массив атрибутов и его значений
     */
    public static function getTagAttributes($string)
    {
        $pattern = '#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#';
        preg_match_all($pattern, $string, $matches);
        return $matches;
    }

    /**
     * Возвращает массив всех элементов, найденных по тегу
     * @param  string $string Строка для поиска
     * @throws ParseException Ошибка парсинга
     * @return array          Массив элементов
     */
    public static function getTagsArray($string, $tag)
    {
        $pattern = "/<$tag\b[^>]*>(.*?)<\/$tag>/is";
        preg_match_all($pattern, $string, $matches);
        if (!isset($matches[1])) {
            throw new ParseException("Не удаётся найти элементы $tag", 1);
        }

        if (count($matches[1]) == 0) {
            return [];
        }

        return $matches[1];
    }

    /**
     * Возвращает значение атрибута по его имени
     * @param  string $string        Строка с элементом
     * @param  string $attributeName Ключ атрибута
     * @return string                Значение атрибута
     */
    public function getTagAttribute($string, $attributeName)
    {
    	$attributes = static::getTagAttributes($string);
    	return preg_replace("/['\"]+/", "", $attributes[2][array_search($attributeName, $attributes[1])]);
    }

}

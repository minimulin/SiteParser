<?php

namespace SiteParser\Utils;

/**
 * Вспомогательный класс для вывода цветных сообщений в консоль
 */
class Echoer
{

    /**
     * Успешное сообщение. На зелёном фоне
     * @param  string $message Текст сообщения
     */
    public static function success($message)
    {
        static::echoColoredMessage($message, '[42m');
    }

    /**
     * Информационное сообщение. На голубом фоне
     * @param  string $message Текст сообщения
     */
    public static function info($message)
    {
        static::echoColoredMessage($message, '[44m');
    }

    /**
     * Сообщение об ошибке. На красном фоне
     * @param  string $message Текст сообщения
     */
    public static function error($message)
    {
        static::echoColoredMessage($message, '[41m');
    }

    /**
     * Предупреждение. На оранжевом фоне
     * @param  string $message Текст сообщения
     */
    public static function warning($message)
    {
        static::echoColoredMessage($message, '[43m');
    }

    public function line()
    {
        echo "\n";
    }

    /**
     * Осуществляет форматирование сообщения и последующий его вывод
     * @param  string $message Текст сообщения
     * @param  string $colorCode Код цвета фона
     */
    protected function echoColoredMessage($message, $colorCode)
    {
        echo date('h:i:s') . ' ' . chr(27) . "$colorCode" . "$message" . chr(27) . "[0m \n";
    }

}

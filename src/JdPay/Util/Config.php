<?php

namespace Payment\JdPay\Util;

class Config {

    private static $settings = array();

    public static function load($file = '') {
        if (file_exists ( $file ) == true) {
            self::$settings = require $file;
        } else {
            if (defined('CONF_PATH')) {
                $path = realpath(CONF_PATH) . '/jdpay.php';
            } else {
                $path = dirname(__DIR__) . '/config/jdpay.php';
            }

            self::$settings = require $path;
        }
    }

    /**
     * 获取某些设置的值
     *
     * @return unknown
     */
    public static function get($key) {
        if (empty(self::$settings)) {
            self::load();
        }

        if (isset(self::$settings[$key])) {
            return self::$settings[$key];
        }

        return '';
    }

    public static function get_trade_num() {
        return self::get ('merchantNum') . self::getMillisecond();
    }

    public static function getMillisecond() {
        list ( $s1, $s2 ) = explode (' ', microtime());
        return ( float ) sprintf ( '%.0f', (floatval ( $s1 ) + floatval ( $s2 )) * 1000 );
    }
}
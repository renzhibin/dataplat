<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/6/22
 * Time: 20:41
 */
/**
 * 全局范围的 var_dump() 简写方式
 */
function d()
{
    $arr = func_get_args();
    echo '<pre>';
    var_dump($arr[0]);
    echo '</pre>';
    exit();
}

/**
 * 更佳简易的原始输出方式
 */
function p()
{
    $arr = func_get_args();
    echo '<pre>';
    print_r($arr[0]);
    echo '</pre>';
    exit();
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

function startsWith($haystack, $needles)
{
    foreach ((array) $needles as $needle) {
        if ($needle != '' && strpos($haystack, $needle) === 0) {
            return true;
        }
    }

    return false;
}

function endsWith($haystack, $needles)
{
    foreach ((array) $needles as $needle) {
        if ((string) $needle === substr($haystack, -strlen($needle))) {
            return true;
        }
    }

    return false;
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (startsWith($value, '"') && endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
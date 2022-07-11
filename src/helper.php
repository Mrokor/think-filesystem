<?php
if (!function_exists('str_contains')) {
    /**
     * 兼容PHP8语法
     * @param $haystack
     * @param $needle
     * @return bool
     */
    function str_contains($haystack, $needle): bool
    {
        return ('' === $needle || false !== strpos($haystack, $needle));
    }
}

<?php

if (!function_exists('format_price')) {
    function format_price($amount)
    {
        return  number_format($amount, 2) . ' SYP';
    }
}

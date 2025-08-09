<?php

if (!function_exists('number')) 
{
    function number($param)
    {
        return number_format(intval($param), 0, '', '.');
    } 
}

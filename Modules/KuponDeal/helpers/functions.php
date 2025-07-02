<?php

if (!function_exists('getDiscountedTotal')) {
    function getDiscountedTotal($amount, $discountType, $discountValue) {
        return $discountType === 'percentage' ? $amount - ($amount * $discountValue / 100) : $amount - $discountValue;
    }
}

if (!function_exists('getDiscountedAmount')) {
    function getDiscountedAmount($amount, $discountType, $discountValue) {
        if ($discountType === 'percentage') {
            return ($amount * $discountValue / 100);
        }
        return $discountValue;
    }
}

if(!function_exists('addColorOpacity')){
    function addColorOpacity($color){
        $rgba = '';
        if (strpos($color, 'rgba') !== false) {
            $rgba = str_replace(['rgba(', ')'], '', $color);
            list($r, $g, $b, $a) = explode(',', $rgba);
            $rgba = "rgba($r,$g,$b,0.06)";
        } else {
            $hex = ltrim($color, '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2)); 
            $b = hexdec(substr($hex, 4, 2));
            $rgba = "rgba($r,$g,$b,0.06)";
        }
        return $rgba;
    }
}
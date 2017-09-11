<?php
/**
 * 验证方法集
 * author wangrui@min-fo.com
 * date 2015-01-07
 */

function checkItemName($str, $tagstr, $exp){
    $name_list = explode($tagstr, $str);
    if(count($name_list) == 2){
        foreach($name_list as $item){
            if(preg_match($exp, $item)){
                return true;
            }
            return fasle;
        }
    }
    return false;
}

//是否为姓名
function isName($str){
    //中文名
    if(preg_match("/^[\x{4e00}-\x{9fa5}|·]+$/u", $str)){
        if(mb_strlen($str, 'utf-8') <= 15){
            if(strpos($str, '·')){
                if(checkItemName($str, '·', '/^[\x{4e00}-\x{9fa5}]+$/u')){
                    return true;
                }
                return false;
            }else{
                return true;
            }
        }
        return false;
    }
    else{
        if(preg_match("/^[a-zA-Z·.\s]+$/", $str)){
            if(strlen($str) <= 30){
                if(strpos($str, '·')){
                    if(checkItemName($str, '·', '/^[a-zA-Z]+$/')){
                        return true;
                    }
                    return false;
                }elseif(strpos($str, '.')){
                    if(checkItemName($str, '.', '/^[a-zA-Z]+$/')){
                        return true;
                    }
                    return false;
                }elseif(strpos($str, ' ')){
                    if(checkItemName($str, ' ', '/^[a-zA-Z]+$/')){
                        return true;
                    }
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }
}

//是否为邮编
function isYzcode($str){
    if(preg_match("/^[1-9]{1}[0-9]{5}$/", $str)){
        return true;
    }
    return false;
}

//是否为地址
function isAddress($str){
    if(preg_match('/^[\x{4e00}-\x{9fa5}|a-zA-Z0-9\(\)\-\（\）\_\s]+$/u', $str)){
        return true;
    }
    return false;
}

//是否为整数或小数
function isPoint($str, $intLen = 8, $floatLen = 2){
    $int_exp = "/^[0-9]{1}[0-9]{0,".($intLen-1)."}$/";
    $float_exp = "/^[0-9]{0,".$floatLen."}$/";

    if(strpos($str, ".") === false){
        if(preg_match($int_exp, $str)){
            return true;
        }
    }
    else{
        $str_array = explode(".", $str);
        if(count($str_array) == 2){
            $int_str = $str_array[0];
            $float_str = $str_array[1];

            if(preg_match($int_exp, $int_str) && preg_match($float_exp, $float_str)){
                return true;
            }
        }
    }

    return false;
}


//是否为平台货号
function isGoodsNum($str){
    if(preg_match('/^[0-9]+$/', $str)){
        return true;
    }
    return false;
}

//是否为用户名
function isUser($str){
    if(preg_match("/^[0-9a-zA-Z]+$/", $str)){
        return true;
    }
    return false;
}

//是否为密码
function isPsw($str){
    if(preg_match("/^[0-9a-zA-Z]+$/", $str)){
        return true;
    }
    return false;
}

//是否为邮箱
function isEmail($str){
    if(preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)){
        return true;
    }
    return false;
}

//是否为手机号
function isMobel($str){
    if(preg_match("/^13[0-9]{1}[0-9]{8}$|14[57]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/", $str)){
        return true;
    }
    return false;
}

//证件号
function isCert($str)
{
    if(preg_match("/^[0-9]+$/", $str)){
        return true;
    }
    return false;
}
?>
<?php
/**
 * 记录极光ID
 * 接口参数: 8段 * 极光ID
 * author wangrui@min-fo.com
 * date 2015-11-13
 */
include_once("../functions_mut.php");

$jpushid = trim($reqlist[8]);
if(empty($jpushid)){
    forExit($lock_array);
    toExit(9, $return_list);
}

if(!file_put_contents($dev_path.'jpush', $jpushid, LOCK_EX)){
    toExit(10, $return_list);
}

forExit($lock_array);
toExit(0, $return_list);

?>

<?php
/**
 * 用户获取验证码
 * 接口参数: 8段 * 手机号
 * author pwj
 * date 2017-06-02
 *
 * editer mo_yu
 * editdate 2017-06-29
 */
include_once("../functions_mut.php");
include_once("../functions_mcheck.php");
include_once("../functions_mdb.php");


//验证参数个数
if (!(count($reqlist) == 9)) {
    forExit($lock_array);
    toExit(9, $return_list);
}

$tel = trim($reqlist[8]);
if (!isMobel($tel)) {
    forExit($lock_array);
    toExit(13, $return_list);
}

//用电话
$user_lockname = $j_path . 'lock/' . $tel;
if (is_file($user_lockname)) {
    forExit($lock_array);
    toExit(15, $return_list);
}
if (!file_put_contents($user_lockname, " ", LOCK_EX)) {
    forExit($lock_array);
    toExit(15, $return_list);
}
$lock_array[] = $user_lockname;

///////////////////修改开始////////////////////////

//生成随机验证码
$code = createCode();
$result = sendMsg($tel, $code);

$return_list=array();
if ($result === true) {
    //保存验证码
    $tel_path = $j_path . 'tel/' . getSubPath($tel, 4, true);
    if (!is_dir($tel_path)) {
        mkdirs($tel_path);
    }

    file_put_contents($tel_path . '/code', $code);

    
    forExit($lock_array);
    toExit(0, $return_list);
} else {
    forExit($lock_array);
    toExit(49, $return_list);
}

///////////////////修改结束////////////////////////

?>
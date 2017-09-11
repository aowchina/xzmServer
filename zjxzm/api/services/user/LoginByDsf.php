<?php
/**
 * 用户登录(电话验证码)
 * 接口参数:8段 * openid * 第三方登录类型(int) * name * 头像
 * author pwj
 * date 2017-06-02
 */
include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../Easemob.class.php");

//验证参数个数
if(!(count($reqlist) == 12)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证openid
$openid = trim($reqlist[8]);
if(empty($openid)){
    forExit($lock_array);
    toExit(13, $return_list);
}

//验证登录类型
$type = trim($reqlist[9]);
if(!in_array($type,[1,2,3])){
   forExit($lock_array);
   toExit(14, $return_list);
}

$name = getStrFromByte(trim($reqlist[10]));
if (strlen($name) > 255) {
    forExit($lock_array);
    toExit(12, $return_list);
}
$url = trim($reqlist[11]);

//连接db
$con = conDb();
if ($con == '') {
    forExit($lock_array);
    toExit(300, $return_list);
}

//检查连接数
if (!checkDbCon($con)) {
    forExit($lock_array, $con);
    toExit(301, $return_list);
}
/* 测试数据
$count = 0;
*/
//openid是否存在
$condition = "typeid = $type and openid = '" . mysql_real_escape_string($openid, $con) . "'";
$count = dbCount('zj_user_dsf', $con, $condition);

//已经登陆过
if ($count > 0) {
    //用户注册过,走登录流程
    $sql = "select userid from zj_user_dsf where $condition";
    $user_info = dbLoad(dbQuery($sql, $con), true);
    $userid = $user_info['userid'];

    //打用户锁
    $user_lockname = $j_path . 'lock/' . $userid;
    if (is_file($user_lockname)) {
        forExit($lock_array);
        toExit(11, $return_list);
    }
    if (!file_put_contents($user_lockname, " ", LOCK_EX)) {
        forExit($lock_array);
        toExit(11, $return_list);
    }
    $lock_array[] = $user_lockname;

    //检查是否有其它设备登录此号
    $condition = "userid = $userid and deviceid != '" . $deviceid . "' and status = 1 and is_app = 0";
    $count = dbCount('zj_user_login', $con, $condition);
    if ($count > 0) {
        $data_out['status'] = 0;
        dbUpdate($data_out, 'zj_user_login', $con, $condition);
    }

    //检查是否有其它人在此设备已登录
    $condition = "userid != $userid and deviceid = '" . $deviceid . "' and status = 1 and is_app = 0";
    $count = dbCount('zj_user_login', $con, $condition);
    if ($count > 0) {
        $data_out['status'] = 0;
        dbUpdate($data_out, 'zj_user_login', $con, $condition);
    }

    //更新当前用户登录状态
    $condition = "userid = $userid and deviceid = '$deviceid' and is_app = 0 ";
    $data_in['status'] = 1;
    dbUpdate($data_in, 'zj_user_login', $con, $condition);

    //更新登录时间
    $now_time = time();
    $data = array();
    $data['lastvisitDate'] = $now_time;
    dbUpdate($data, 'zj_seller', $con, "sellerid = $userid");

    $user_path = $j_path . 'user/' . getSubPath($userid, 4, true);
    if (!is_dir($user_path)) {
        mkdirs($user_path);
    }
    /*********返回参数修改**********/
    $sql = "select name,picture from zj_seller where sellerid=".$userid;
    $userData = dbLoad(dbQuery($sql, $con), true);

    //返回参数
    $data = array();
    $data['userid'] = $userid;
    $data['nickname'] = $userData['name'];
    $data['picture'] = empty($userData['picture']) ? '' : $userData['picture'];
    /*********返回参数修改**********/

    forExit($lock_array, $con);
    $return_list['data'] = json_encode($data);
    toExit(0, $return_list, true);

} else {
    //锁用户表
    $user_lock = $j_path . 'lock/user';
    //锁表
    if (!lockDb($user_lock, 3)) {
        forExit($lock_array, $con);
        toExit(303, $return_list);
    }
    $lock_array[] = $j_path . "lock/user";

    //取出即将插入的id
    $sql = "SHOW TABLE STATUS like 'zj_seller'";
    $tabelStatus = dbLoad(dbQuery($sql, $con), true);
    $userid = $tabelStatus['Auto_increment'];

    $nowtime = time();
    $data['sellerid'] = $userid;
    $data['name'] = $name;
    $data['password'] = '';
    $data['picture'] = $url;
    $data['lastvisitDate'] = $data['addtime'] = $nowtime;
    if (dbAdd($data, 'zj_seller', $con)) {
        //是否有其他人在此设备上登录
        $condition = "userid != $userid and deviceid = '" . $deviceid . "' and status = 1 and is_app = 0";
        $count = dbCount('zj_user_login', $con, $condition);
        if ($count > 0) {
            $data_out['status'] = 0;
            dbUpdate($data_out, 'zj_user_login', $con, $condition);
        }

        $data_in['userid'] = $userid;
        $data_in['deviceid'] = $deviceid;
        $data_in['status'] = 1;
        $data_in['is_app'] = 0;
        dbAdd($data_in, 'zj_user_login', $con);

        $data_in_dsf['userid'] = $userid;
        $data_in_dsf['typeid'] = $type;
        $data_in_dsf['openid'] = mysql_real_escape_string($openid, $con);//转义mysql中的字符
        $data_in_dsf['intime'] = time();
        dbAdd($data_in_dsf, 'zj_user_dsf', $con);

        $user_path = $j_path . 'user/' . getSubPath($userid, 4, true);
        if (!is_dir($user_path)) {
            mkdirs($user_path);
        }
        // /*********返回参数修改**********/
        // $sql = "select name,picture from zj_seller where sellerid=".$userid;
        // $userData2 = dbLoad(dbQuery($sql, $con), true);

        // //返回参数
        // $data = array();
        // $host = $_SERVER['HTTP_HOST'];
        // $data['userid'] = $userid;
        // $data['nickname'] = $userData2['name'];
        // $data['picture'] = $host.'/zjxzm/'.$userData2['picture'];
        // /*********返回参数修改**********/

        /***************注册环信开始********************/
        $sql = "select name from zj_seller where sellerid=".$userid;
        $name = dbLoad(dbQuery($sql,$con),true)['name'];

        //注册到环信
        $hx = new Easemob($hx);
        $create_result = $hx->createUser('sell'.$userid, $openid, $name);
        // var_dump($create_result);exit;
        if($create_result){
            if(isset($create_result['error'])){
                if($create_result['error'] != 'duplicate_unique_property_exists'){
                    forExit($lock_array, $con);
                    toExit(11, $return_list);
                }
                else{
                    //重置环信登录密码与昵称
                    $hx->resetPassword($userid, $openid);
                    $hx->editNickname($userid, $name);
                }
            }
        }else{
            forExit($lock_array, $con);
            toExit(63, $return_list);//环信注册失败
        }
        /***************注册环信结束********************/
        $return=array();
        $return['userid'] = $data['sellerid'];
        $return['nickname'] = $data['name'];
        $return['picture'] = $data['picture'];
        
        forExit($lock_array, $con);
        $return_list['data'] = json_encode($return);
        toExit(0, $return_list, true);
    } else {
        forExit($lock_array, $con);
        toExit(302, $return_list);
    }
}
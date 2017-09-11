<?php
/** 
 * 修改昵称和图片
 * 接口参数：8段 * userid * 姓名（需转）
 * author pwj
 * date 2017-06-03
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 10)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//姓名
$name = getStrFromByte(trim($reqlist[9]));

if(!isName($name)){
    forExit($lock_array);
    toExit(36, $return_list);
}


//验证userid
$userid = intval(trim($reqlist[8]));
if(!($userid >= 1)){
    forExit($lock_array);
    toExit(10, $return_list);
}



//userid打锁
$user_cpath = getSubPath($userid, 4, true);
$user_path = $j_path.'user/'.$user_cpath;
if(!is_dir($user_path)){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(is_file($user_path.'lock')){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(!file_put_contents($user_path.'lock', " ", LOCK_EX)){
    forExit($lock_array);
    toExit(11, $return_list);
}
$lock_array[] = $user_path.'lock';

//连接db
$con = conDb();
if($con == ''){
    forExit($lock_array);
    toExit(300, $return_list);
}

//检查连接数
if(!checkDbCon($con)){
    forExit($lock_array, $con);
    toExit(301, $return_list);
}
$data = array();
//头像上传路径
//验证图片格式
if(count($_FILES) >0)
{
    file_put_contents("../WxPay/logs/".date('Y-m-d').'.log',date('Y-m-d H:i:s').$_FILES.'<br>',0777);
    $up_filename = trim($_FILES['img0']['name']);
    $img_suffix = getImgType($up_filename);
    if(!($img_suffix == 'png' || $img_suffix == 'jpg' || $img_suffix == 'jpeg')){
        forExit($lock_array);
        toExit(90, $return_list);
    }

    file_put_contents("../WxPay/logs/".date('Y-m-d').'.log',date('Y-m-d H:i:s').$up_filename.'<br>',0777);
    
    $now_time = time();
    $new_filename = $now_time.".".$img_suffix;
    $up_path = $s_path."downLoad/appuser/".$user_cpath;

    //记录日志
    file_put_contents("../WxPay/logs/".date('Y-m-d').'.log',date('Y-m-d H:i:s').$up_path.'<br>',0777);

    if(!umask(mkdirs($up_path))){
        forExit($lock_array, $con);
        toExit(91, $return_list);
    }
    //记录日志
    file_put_contents("../WxPay/logs/".date('Y-m-d').'.log',date('Y-m-d H:i:s').$_FILES['img0']['tmp_name'].'<br>',0777);
    
    if(!move_uploaded_file($_FILES['img0']['tmp_name'], $up_path.$new_filename)){
        forExit($lock_array, $con);
        toExit(92, $return_list);
    }


    $data['picture'] = "downLoad/appuser/".$user_cpath.$new_filename;
}
$sql = "select picture,name from zj_appuser where appuid = $userid";
$user_info = dbLoad(dbQuery($sql, $con), true);

$data['name'] = $name;

if(dbUpdate($data, 'zj_appuser', $con, "appuid = $userid")){
    //删除原来的图片
    if(isset($data['picture']))
    {
        $old_img = $s_path.$user_info['picture'];
        if(is_file($old_img))
        {
            unlink($old_img);
        }
    }
    
    //新加返回信息
    $sql = "select picture from zj_appuser where appuid = $userid";
    $user_info_new = dbLoad(dbQuery($sql, $con), true);

    $arr['name'] = $name;
    $arr['picture'] = $user_info_new['picture'] ? $user_info_new['picture'] : " ";

    $return_list['data'] = json_encode($arr);
}
else
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);

?>

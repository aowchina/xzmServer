<?php
/**
 * 认证
 * 接口参数：8段 * userid * 公司名称(需转) * 姓名(需转) * 证件号 * 省id * 市id * 区id * 范围(需转) * 专长(需转)
 * author pwj
 * date 2017-06-05
 */

include_once("../functions_mut.php");
include_once("../functions_mcheck.php");
include_once("../functions_mdb.php");

//验证参数个数
if(!(count($reqlist) == 17)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//公司名称
$company = getStrFromByte(trim($reqlist[9]));
if(!isName($company)){
    forExit($lock_array);
    toExit(37, $return_list);
}

//姓名
$name = getStrFromByte(trim($reqlist[10]));
if(!isName($name)){
    forExit($lock_array);
    toExit(36, $return_list);
}

//证件号
$cert = trim($reqlist[11]);
if(!isCert($cert )){
    forExit($lock_array);
    toExit(13, $return_list);
}

//省id
$pid = intval(trim($reqlist[12]));
if($pid < 1){
    forExit($lock_array);
    toExit(14, $return_list);
}

//市
$cid = intval(trim($reqlist[13]));
if($cid < 1){
    forExit($lock_array);
    toExit(15, $return_list);
}

//区
$aid = intval(trim($reqlist[14]));
if($aid < 0){
    forExit($lock_array);
    toExit(16, $return_list);
}

//范围
$range = getStrFromByte(trim($reqlist[15]));
if(empty($range)){
    forExit($lock_array);
    toExit(38, $return_list);
}

//专长
$specialty = getStrFromByte(trim($reqlist[16]));
if(empty($range)){
    forExit($lock_array);
    toExit(39, $return_list);
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
$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
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

//图片保存路径
$data = [];
$up_path = $s_path."downLoad/seller/".$user_cpath;
if(!is_dir($up_path))
{
    if(!mkdirs($up_path)){
        forExit($lock_array, $con);
        toExit(91, $return_list);
    }
}

//保存图片的公共路径
$public_url = "downLoad/seller/".$user_cpath;
$imgamount = count($_FILES);
if($imgamount > 0){
    //文件上传
    for($i = 0; $i < $imgamount; $i++){
        $up_filename = trim($_FILES['img'.$i]['name']);
        $img_suffix = getImgType($up_filename);  //图片后缀
        if(!($img_suffix == 'png' || $img_suffix == 'jpg' || $img_suffix == 'jpeg')){
            forExit($lock_array);
            toExit(90, $return_list);
        }
        if($i == 0)
        {
            $now_time = time();
            $new_filename = $now_time.".".$img_suffix;
        }
        else
        {
            $new_filename = $i.".".$img_suffix;
        }


        move_uploaded_file($_FILES['img'.$i]['tmp_name'], $up_path.$new_filename);

        switch($i)
        {
            case 0:
                $data['picture'] = $public_url.$new_filename;
                break;
            case 1:
                $data['cardfront'] = $public_url.$new_filename;
                break;
            case 2:
                $data['cardback'] = $public_url.$new_filename;
                break;
            case 3:
                $data['cardhand'] = $public_url.$new_filename;
                break;
            case 4:
                $data['license'] = $public_url.$new_filename;
                break;
        }

    }
}

$data['sname'] = $name;
$data['pid'] = $pid;
$data['cid'] = $cid;
$data['qid'] = $aid;
$data['company'] = $company;
$data['major'] = $range;
$data['number'] = $cert;
$data['skill'] = $specialty;
$sql = "select picture from zj_appuser where sellerid = $userid";
$user_info = dbLoad(dbQuery($sql, $con), true);

if(dbUpdate($data, 'zj_seller', $con, "sellerid = $userid")){
    //删除原来的图片
    $old_img = $s_path.$user_info['picture'];
    if(is_file($old_img))
    {
        unlink($old_img);
    }
}
else
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}

forExit($lock_array, $con);
toExit(0, $return_list, false);
?>
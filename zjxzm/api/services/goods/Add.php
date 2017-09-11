<?php
/**
 * 发布配件 
 * 接口参数: 8段 * userid * 车款id * 商品类别id * OEM编码 * price * tel * 详情(需转) * 名称(需转) * 配件类别
 * author pwj
 * date 2017-06-01
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");

//验证参数个数
if(!(count($reqlist) == 17)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证车款id
$carid = trim($reqlist[9]);
$carid = rtrim($carid,',');

//验证类别id
$typeid = trim($reqlist[10]);
if($typeid< 1 || $typeid > 4294967296){
    forExit($lock_array);
    toExit(51, $return_list);
}

$ptid = trim($reqlist[16]);
if($ptid< 1 || $ptid > 4294967296){
    forExit($lock_array);
    toExit(51, $return_list);
}

//验证OEM编码
$oemid = trim($reqlist[11]);
if(empty($oemid)){
    forExit($lock_array);
    toExit(52, $return_list);
}

//验证价格
$price = trim($reqlist[12]);
if(!isPoint($price)){
    forExit($lock_array);
    toExit(53, $return_list);
}

//验证电话
$tel = trim($reqlist[13]);
if(!isMobel($tel)){
    forExit($lock_array);
    toExit(54, $return_list);
}

//验证详情
$detail = getStrFromByte(trim($reqlist[14]));
if(empty($detail)){
    forExit($lock_array);
    toExit(55, $return_list);
}

//验证详情
$name = getStrFromByte(trim($reqlist[15]));
if(empty($name)){
    forExit($lock_array);
    toExit(49, $return_list);
}

//验证userid
$userid = trim($reqlist[8]);
if($userid < 1 || $userid > 4294967296){
    forExit($lock_array);
    toExit(10, $return_list);
}

$user_path = $j_path.'user/'.getSubPath($userid, 4, true);
if(!is_dir($user_path)){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(is_file($user_path."lock")){
    forExit($lock_array);
    toExit(11, $return_list);
}
if(!file_put_contents($user_path."lock", " ", LOCK_EX)){
    forExit($lock_array);
    toExit(11, $return_list);
}
$lock_array[] = $user_path."lock";
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

//取出店铺id
$sql = "select shopid from zj_shop where sellerid = $userid and state = 1";
$shopid =  dbLoad(dbQuery($sql, $con), true);

if(empty($shopid))
{
    forExit($lock_array, $con);
    toExit(56, $return_list);
}

//保存图片路径
$Y = date('Y');
$m = date('m');
$d = date('d');
$up_path = $s_path."images/goods/".$Y.'/'.$m.'/'.$d.'/';
if(!is_dir($up_path))
{
    mkdirs($up_path);
}
$imageArr = [];
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
        $new_filename = $i.'_'.time().'.'.$img_suffix;
        move_uploaded_file($_FILES['img'.$i]['tmp_name'], $up_path.$new_filename);
        $imageArr[] = "images/goods/".$Y.'/'.$m.'/'.$d.'/'.$new_filename;
    }
}


$data = [];
$data['shopid'] = $shopid['shopid'];
$data['carid'] = $carid;
$data['typeid'] = $typeid;
$data['oem'] = $oemid;
$data['price'] = $price;
$data['tel'] = $tel;
$data['detail'] = $detail;
$data['img'] = json_encode($imageArr);
$data['addtime'] = time();
$data['name'] = $name;
$data['state'] = 2;
$data['is_sj'] = 2;
$data['ptid'] = $ptid;
if(!dbAdd($data, 'zj_good', $con))
{
    forExit($lock_array, $con);
    toExit(302, $return_list);
}



forExit($lock_array, $con);
toExit(0, $return_list, false);


?>



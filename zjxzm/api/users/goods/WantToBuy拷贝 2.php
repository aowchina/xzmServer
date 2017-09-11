<?php
/**
 * 求购配件
 * 接口参数: 8段 * userid * 品牌名称(转) * 车系名称(转) * 车款名称(转) * vin * name(转) * 品质类别(多个以,相连) * 品牌限定(转) * 其他(转) * img(品牌过来的图片)
 * author pwj
 * date 2017-06-06
 */

include_once("../functions_mut.php");
include_once("../functions_mdb.php");
include_once("../functions_mcheck.php");
include_once("../Jpush.php");

//验证参数个数
if(!(count($reqlist) == 18)){
    forExit($lock_array);
    toExit(9, $return_list);
}

//验证品牌
$bname = getStrFromByte(trim($reqlist[9]));
if(empty($bname))
{
    forExit($lock_array);
    toExit(40, $return_list);
}

//验证车系id
$sname = getStrFromByte(trim($reqlist[10]));
if(empty($sname))
{
    forExit($lock_array);
    toExit(41, $return_list);
}

//验证车款id
$cname = getStrFromByte(trim($reqlist[11]));
if(empty($sname))
{
    forExit($lock_array);
    toExit(42, $return_list);
}

//验证vin
$vin = trim($reqlist[12]);
if(empty($vin)){
    forExit($lock_array);
    toExit(60, $return_list);
}

//验证配件名称
$name = getStrFromByte(trim($reqlist[13]));
if(empty($name)){
    forExit($lock_array);
    toExit(61, $return_list);
}

$type = rtrim($reqlist[14],',');

$blimit = getStrFromByte(trim($reqlist[15]));

$other = getStrFromByte(trim($reqlist[16]));

$bimg = trim($reqlist[17]);

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
//验重
/*ceshi 
$userid = 1;
$bname= "问问";
$sname= "问问2";
$cname= "问问sd";
$name= "问dwd问";
*/

$count = dbCount('zj_border',$con, "appuid = $userid and bname = '$bname' and sname = '$sname' and cname = '$cname' and jname = '$name' ");

if($count ==1)
{
    forExit($lock_array);
    toExit(63, $return_list);
}

//处理图片
$Y = date('Y');
$m = date('m');
$d = date('d');
$up_path = $s_path."images/wantbuy/".$Y.'/'.$m.'/'.$d.'/';
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
        $imageArr[] = "images/wantbuy/".$Y.'/'.$m.'/'.$d.'/'.$new_filename;
    }
}

$data = [];
$data['appuid'] = $userid;
$data['bname'] = $bname;
$data['sname'] = $sname;
$data['cname'] = $cname;
$data['vin'] = $vin;
$data['jname'] = $name;
$data['type'] = $type;
$data['img'] = $bimg;
$data['pinpai'] = $blimit;
$data['otherpz'] = $other;
$data['picture'] = json_encode($imageArr);
$res = dbAdd($data, 'zj_border', $con);

if(!$res)
{
    forExit($lock_array);
    toExit(302, $return_list);
}

$sql = "select last_insert_id() as id from zj_border";
$bid = dbLoad(dbQuery($sql, $con),true);

/**************************推送开始*******************************/
//给卖家发送通知
$sql = "select deviceid from zj_user_login where is_app=0 group by deviceid";
$result = dbLoad(dbQuery($sql, $con));

if(count($result) > 0){
    foreach ($result as $key => $value) {
        $s_path = "/var/www/html/zjxzm/sellerdata/";
        //获取极光id
        $jpushid = @file_get_contents($s_path.'device/'.getSubPath($value['deviceid'], 4, true).'jpush');

        if($jpushid){
            $jp = new Jpush();
            $jp->push(array('registration_id'=>array($jpushid)),'您收到一条新的求购信息',array('page'=>1));
        }
    }
}

/**************************推送结束*******************************/

forExit($lock_array, $con);
$return_list['data'] = $bid;  
toExit(0, $return_list, false);

?>
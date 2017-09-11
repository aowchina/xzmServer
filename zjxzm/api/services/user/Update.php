<?php
/**
 * 获取应用版本信息
 * 接口参数: 8段*版本号(vnum)
 * author min-fo25
 * date 2017-4-5
 */
include_once("../functions_mut.php");

//获取参数,参数为应用版本号
//    $version_num = $_POST["vnum"];
      $version_num = $reqlist[8];
//判断$version_num合法性
    if(is_numeric($version_num))
    {
        //拼xml文件路径
        $version_file = $s_path.'zjxzm.xml';

        //判读xml文件是否存在
        if(is_file($version_file)){
            //读取xml文件中版本信息和新版本路径信息
            $version_con= file_get_contents($version_file);

            $xml = new DOMDocument();
            $xml->load($version_file);
            $root = $xml->documentElement;
            $nodes = $root->getElementsByTagName("crpuser");
            $num = $nodes->item(0)->getAttribute('version');
            $url = $nodes->item(0)->getAttribute('weburl');

            $res_list = array();

            //比较版本号,如果xml版本号大于获得参数代表的版本号,则返回errorcode,提示需要更新,同时返回新版本          下载地址;否则返回errorcode提示不需要更新
            if($num > $version_num){
                $res_list["data"]["url"] = $url;
                $res_list["data"]["num"] = $num;
                $res_list["errorcode"] = 0;
             
            }
            else{
       
                 $res_list["errorcode"] = 21;
            }
        }
        else{
              
             $res_list["errorcode"] =68;
        }
    }
    else
    {
         $res_list["errorcode"] = 69;
    }

    echo json_encode($res_list);
   forExit($lock_array, $con);
    exit;


?>

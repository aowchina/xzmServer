<?php
/* 
 * 数据库操作方法集
 * author wangrui@min-fo.com
 * date 2015-10-30
 */


//更新记录
function dbUpdate($update_array, $table, $con, $where){
    $str = '';
    foreach($update_array as $key => $value){
        $str .= $key."='".$value."',";
    }
    $str = substr($str, 0, -1);

    $sql = "update $table set $str where $where";
    return dbQuery($sql, $con);
}

//插入记录
function dbAdd($add_array, $table, $con){
    $str_1 = '';
    $str_2 = '';
    foreach($add_array as $key => $value){
        $str_1 .= $key.",";
        $str_2 .= "'$value',";
    }

    $str_1 = substr($str_1, 0, -1);
    $str_2 = substr($str_2, 0, -1);

    $sql = "insert into $table ($str_1) values ($str_2)";
    
    return dbQuery($sql, $con);
}

//从结果集中获取记录
function dbLoad($result, $isFind = false){
    $return_array = array();
    $i = 0;
    while($row = @mysql_fetch_array($result, MYSQL_ASSOC)){
        $return_array[$i] = $row;
        $i++;
    }
    
    if($isFind){
        return @$return_array[0];
    }
    return $return_array;
}

//结果集中获取记录返回一维数组
function dbResult($result){
    $return_array = array();
    while (list($n) = mysql_fetch_row($result))
    {
        $return_array[]=$n;
    }
    return $return_array;
}

//获取sql语句结果集
function dbQuery($sql, $con){
   
    return mysql_query($sql, $con);
}

//查询记录个数
function dbCount($table, $con, $where = ''){
    if(!empty($where)){
        $where = " where ".$where;
    }
    $sql = "select count(*) from $table".$where;
    
    $result = mysql_query($sql, $con);
    $result_array = @mysql_fetch_array($result, MYSQL_NUM);
    return $result_array[0];
}

//锁表
function lockDb($lock_filename, $try_amount = 3){
    if(is_file($lock_filename))
    {
        if($try_amount > 0){
            $sleep_time = '1.'.rand(0, 9);
            sleep($sleep_time);
            lockDb($lock_filename, $try_amount - 1);
        }else{
            return false;
        }
    }
    else{
        if(!file_put_contents($lock_filename, ' ', LOCK_EX))
        {
            if($try_amount > 0){
                $sleep_time = '1.'.rand(0, 9);
                sleep($sleep_time);
                lockDb($lock_filename, $try_amount - 1);
            }else{
                return false;
            }
        }
        else
        {
            return true;
        }
    }
}

//检查数据库连接数
function checkDbCon($con){
    $result = mysql_query("show status like 'Threads_connected'", $con);
    $result_array = mysql_fetch_array($result, MYSQL_NUM);
    if($result_array[1] < 80){
        return true;
    }
    return false;
}

//关闭数据库
function closeDb($con){
    mysql_close($con);
}

//连接数据库
function conDb(){
    $db_host = "218.240.21.181";
    $db_root = "root";
    $db_psw = "g3hao.com";
    $db_name = "zj";

    $con = mysql_connect($db_host, $db_root, $db_psw);
    if($con){
        mysql_select_db($db_name, $con);

        mysql_query("SET NAMES UTF8", $con);
        mysql_query("set character_set_client=utf8", $con); 
        mysql_query("set character_set_results=utf8", $con);

        return $con;
    }
    return '';
}

//删除记录
function dbDel($table, $con, $where){
    $sql = "delete from ".$table." where $where";
    return dbQuery($sql, $con);
}

?>
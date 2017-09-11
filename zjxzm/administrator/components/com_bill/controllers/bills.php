<?php

defined('_JEXEC') or die;

//通告 组件名.Controller
class BillControllerBills extends JControllerAdmin{

    public function getModel($name = 'Bill', $prefix = 'BillModel', $config = array('ignore_request'=>true)){
        $model = parent::getModel($name, $prefix, $config);
        return $model;
    }

    public function pay()
    {
        $ids = $this->input->get('cid', array(), 'array');
        $db = JFactory::getDbo();

        foreach($ids as $k => $id)
        {
            //判断orderid是求购还是商城
            $sql = "select orderid from zj_bill where id=".$id;
            $db->setQuery($sql);
            $res = $db->loadAssoc();

            if(substr($res['orderid'], 0,4) == 'zjqg'){
                //根据orderid类型，查询相关信息
                $sql = "select a.state,c.sellerid,c.price as money from zj_bill as a left join zj_qgorder as b on a.orderid = b.qgorderid left join zj_setmoney as c on c.id = b.bjid left join zj_seller as d on c.sellerid = d.sellerid where a.id = $id";
                $db->setQuery($sql);
                $billinfo = $db->loadAssoc();
            }else{
                //根据账单表取出配件商id(商城)
                $sql = "select a.state,c.sellerid,b.money from zj_bill as a left join zj_order as b on a.orderid = b.orderid left join zj_seller as c on b.shopid = c.shopid where a.id = $id";
                $db->setQuery($sql);
                $billinfo = $db->loadAssoc();
            }
            
            //判断用户是否已经打款
            if($billinfo['state'] == '1'){

                $this->setMessage("该用户已经打款!!");
                $this->setRedirect(JRoute::_('index.php?option=com_bill&view=bills', false));

            }else{
                    //查询该用户
                    $before_money=array();
                    if($billinfo['sellerid']){
                        //取出用户钱包中的钱
                        $sql = "select money from #__wallet where userid = ".$billinfo['sellerid']." and tid = 2";
                        $db->setQuery($sql);
                        $before_money = $db->loadResult();
                    }
                        
                    //向用户账户转账
                    $time= time();
                    if(empty($before_money))
                    {
                        //插入
                        $sql = "insert into #__wallet (userid,money,addtime,tid) values(".$billinfo['sellerid'].",".$billinfo['money'].",$time,2)";
                    }
                    else
                    {
                        //更新
                        $now_money = $before_money + $billinfo['money'];
                        $sql = "update #__wallet set money = $now_money where userid = $billinfo[sellerid] and tid = 2";
                    }
                    $db->setQuery($sql);
                    $success = $db->query();
                    if($success === true)
                    {
                        $sql = "update #__bill set state = 1,paytime = $time  where id = $id";
                        $db->setQuery($sql);
                        $db->query();
                    }
                    $this->setMessage("操作成功!");
                    $this->setRedirect(JRoute::_('index.php?option=com_bill&view=bills', false));
                }
                

            }

        }
}

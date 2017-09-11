<?php
defined('_JEXEC') or die;

class OrderModelOrders extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'id', 'a.id',
                'appuid', 'a.appuid',
                'orderid', 'a.orderid',
                'shopid', 'a.shopid',
                'addtime', 'a.addtime',
                'status', 'a.status',
                'money', 'a.money',
                'name', 'a.name',
                'kuaidih', 'a.kuaidih',
                'wlname','a.wlname',
                'paytime', 'a.paytime',
                'paytype', 'a.paytype',
                'ifreceive','a.ifreceive',
                'pid','a.pid',
                'cid','a.cid',
                'qid','a.qid',
                'address','a.address',
                'retime','a.retime',
                'sname','a.sname',
                'stel','a.stel',
                'goodid', 'a.goodid',
			);
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $search2 = $this->getUserStateFromRequest($this->context.'.filter.search2', 'filter_search2');
        $this->setState('filter.search2', $search2);

//        $search3 = $this->getUserStateFromRequest($this->context.'.filter.search3', 'filter_search3');
//        $this->setState('filter.search3', $search3);
//
//        $wl_id = $this->getUserStateFromRequest($this->context.'.filter.wl', 'filter_wl', '', 'string');
//        @$this->setState('filter.wl', $published);
//
        $status = $this->getUserStateFromRequest($this->context.'.filter.status', 'filter_status', '', 'string');
        @$this->setState('filter.status', $published);

//        $group = $this->getUserStateFromRequest($this->context.'.filter.group', 'filter_group', '', 'string');
//        @$this->setState('filter.group', $published);

        parent::populateState('a.id', 'desc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.id, a.appuid, a.orderid, a.shopid, a.addtime, a.retime, a.stel,a.sname, a.status, a.money , a.kuaidih, a.wlname, a.paytime , a.paytype, a.ifreceive, a.pid,a.cid,a.qid,a.address, a.goodid, a.info'));
    	$query->from($db->quoteName('#__order').' AS a');

        //$query->where('a.status > 0');

        $status = $this->getState('filter.status');
        if (is_numeric($status)){
            $query->where('a.status = '.(int) $status);
        } elseif ($status === ''){
            $query->where('(a.status >= 0)');
        }
        // $query->select('wl.name AS wl_name');
        // $query->leftJoin($db->quoteName('#__wl').' AS wl ON wl.id = a.wl_id');
        // $wl_id = $this->getState('filter.wl');

//         if (is_numeric($wl_id)){
//             $query->where('a.wl_id = '.(int) $wl_id);
//         } elseif ($wl_id === ''){
//             $query->where('(a.wl_id >= 0)');
//         }


//         Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)){
            if (stripos($search, 'id:') === 0){
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.trim($db->escape($search, true)).'%');
                $query->where('(a.orderid LIKE '.$search.' or a.sname LIKE '.$search.' or a.stel LIKE '.$search.
                    ' or a.kuaidih LIKE '.$search.')');
            }
        }
         
         //多表联查操作
        $query->select('b.name,b.tel');
        $query->join('LEFT', $db->quoteName('#__appuser').' AS b ON a.appuid = b.appuid');

        $query->select('c.shopname');
        $query->join('LEFT', $db->quoteName('#__shop').' AS c ON a.shopid = c.shopid');

        $query->select('d.name as gname');
        $query->join('LEFT', $db->quoteName('#__good').' AS d ON a.goodid = d.goodid');

        // //多表联查操作
        // $query->select('c.username AS usertel,c.name');
        // $query->join('LEFT', $db->quoteName('#__users').' AS c ON c.id = a.userid');
        // //取出会员级别
        // $query->select('e.title AS groupName');
        // $query->join('LEFT',$db->quoteName('#__user_usergroup_map').' AS f on a.userid= f.user_id');
        // $query->join('LEFT',$db->quoteName('#__usergroups').' AS e on e.id= f.group_id');

         $search2 = $this->getState('filter.search2');
         if (!empty($search2)){
             $search2 = $db->Quote('%'.trim($db->escape($search2, true)).'%');
             $query->where('(b.name LIKE '.$search2.' or b.tel LIKE '.$search2.')');
         }

        // $query->select('d.name AS username,d.level AS level');s
        // $query->join('LEFT', $db->quoteName('#__users').' AS d ON d.id = a.userid');






        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
    	return $query;
    }
}
?>
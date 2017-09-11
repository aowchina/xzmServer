<?php
defined('_JEXEC') or die;

class WborderModelWborders extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'id', 'a.id',
                'appuid', 'a.appuid',
                'qgorderid', 'a.qgorderid',
                'bjid', 'a.bjid',
                'addtime', 'a.addtime',
                'status', 'a.status',
                'type','a.type',
                'price', 'a.price',
                'jname', 'a.jname',
                'kuaidih', 'a.kuaidih',
                'wlname','a.wlname',
                'paytime', 'a.paytime',
                'paytype', 'a.paytype',
                'pid','a.pid',
                'cid','a.cid',
                'qid','a.qid',
                'address','a.address',
                'retime','a.retime',
                'sname','a.sname',
                'stel','a.stel',
			);
		}
       	parent::__construct($config);
    }

    protected function populateState($wbordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $search2 = $this->getUserStateFromRequest($this->context.'.filter.search2', 'filter_search2');
        $this->setState('filter.search2', $search2);

        $status = $this->getUserStateFromRequest($this->context.'.filter.status', 'filter_status', '', 'string');
        @$this->setState('filter.status', $published);

        parent::populateState('a.id', 'desc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.id, a.appuid, a.qgorderid, a.bjid, a.addtime, a.retime, a.stel,a.sname, a.status, a.type, a.price , a.kuaidih, a.wlname, a.paytime , a.paytype, a.pid,a.cid,a.qid,a.address'));
    	$query->from($db->quoteName('#__qgorder').' AS a');

        $status = $this->getState('filter.status');
        if (is_numeric($status)){
            $query->where('a.status = '.(int) $status);
        } elseif ($status === ''){
            $query->where('(a.status >= 0)');
        }



        //  Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)){
            if (stripos($search, 'id:') === 0){
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.trim($db->escape($search, true)).'%');
                $query->where('(a.qgorderid LIKE '.$search.' or a.sname LIKE '.$search.' or a.stel LIKE '.$search.
                    ' or a.kuaidih LIKE '.$search.')');
            }
        }
         
         //多表联查操作
        $query->select('b.name,b.tel');
        $query->join('LEFT', $db->quoteName('#__appuser').' AS b ON a.appuid = b.appuid');

        $query->select('c.bid');
        $query->join('LEFT', $db->quoteName('#__setmoney').' AS c ON a.bjid = c.id');

        $query->select('d.jname,d.bname,d.sname,d.cname,d.vin,d.picture');
        $query->join('LEFT', $db->quoteName('#__border').' AS d ON c.bid = d.bid');

    

         $search2 = $this->getState('filter.search2');
         if (!empty($search2)){
             $search2 = $db->Quote('%'.trim($db->escape($search2, true)).'%');
             $query->where('(b.name LIKE '.$search2.' or b.tel LIKE '.$search2.')');
         }

        // $query->select('d.name AS username,d.level AS level');s
        // $query->join('LEFT', $db->quoteName('#__users').' AS d ON d.id = a.userid');




        $wborderCol = $this->state->get('list.wbordering'); 
        $wborderDirn = $this->state->get('list.direction'); 
        $query->wborder($db->escape($wborderCol.' '.$wborderDirn));
    	return $query;
    }
}
?>
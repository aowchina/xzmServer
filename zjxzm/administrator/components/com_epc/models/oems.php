<?php
defined('_JEXEC') or die;

class EpcModelOems extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'id', 'a.id',
                'epcid','a.epcid',
                'oem','a.oem',
           		'name', 'a.name',
                'price', 'a.price',
                'hprice', 'a.hprice',
                'num', 'a.num',
                'position', 'a.position',
                'syear', 'a.syear',
                'eyear', 'a.eyear',
                'xzhuang', 'a.xzhuang',
                'loureplace', 'a.loureplace',
                'lounewjian', 'a.lounewjian',
                'note','a.note',
                'danwei','a.danwei',

			);
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        parent::populateState('a.position', 'desc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.id, a.name,a.syear,a.eyear,a.xzhuang,a.loureplace,a.lounewjian, a.price,a.hprice,a.oem,a.num, a.position,a.note,a.danwei,a.epcid'));
    	$query->from($db->quoteName('#__oem').' AS a');
        $query->select('b.epcname');
        $query->join('LEFT', $db->quoteName('#__epc').' AS b ON b.epcid = a.epcid');

        //接收搜索内容
        $search = $this->getState('filter.search');

        if (!empty(trim($search))){
            if (stripos($search, 'id:') === 0){
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.oem LIKE '.$search.')');
            }
        }


        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        
    	return $query;
    }
}
?>
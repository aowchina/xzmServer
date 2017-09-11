<?php
defined('_JEXEC') or die;

class GoodModelGoods extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'goodid', 'a.goodid',
                'name', 'a.name',
                'oem', 'a.oem',
                 'carid', 'a.carid',
                 'typeid', 'a.typeid',
                 'epcid', 'a.epcid',
                'price', 'a.price',
                'area', 'a.area',
                'original', 'a.original',
                'note', 'a.note',
                'addtime', 'a.addtime',
                'state','a.state',
                'publish_up','a.publish_up',
                'publish_down','a.publish_down',
			); 
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        parent::populateState('a.goodid', 'asc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.goodid, a.name,a.state,a.publish_up,a.publish_down, a.oem, a.price,a.area,a.original,a.note,a.addtime'));
    	$query->from($db->quoteName('#__good').' AS a');


       //  联表查询
//        $query->select('b.epcname,b.typeid');
//        $query->join('LEFT', $db->quoteName('#__epc').' AS b ON b.epcid = a.epcid');
        $query->select('c.tname,c.carid');
        $query->join('LEFT', $db->quoteName('#__type').' AS c ON a.typeid = c.typeid');
        $query->select('d.cname');
        $query->join('LEFT', $db->quoteName('#__car').' AS d ON c.carid = d.carid');
         //接收搜索内容
        $search = $this->getState('filter.search');
        
        if (!empty(trim($search))){
            if (stripos($search, 'goodid:') === 0){
                $query->where('a.goodid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.name LIKE '.$search.' or a.oem LIKE'.$search.')');
            }
        }        

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        

    	return $query;
    }

    
}
?>
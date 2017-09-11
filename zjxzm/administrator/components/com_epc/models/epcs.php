<?php
defined('_JEXEC') or die;

class EpcModelEpcs extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'epcid', 'a.epcid',
                'epcname', 'a.epcname',
                'epctid', 'a.epctid',
           		'typeid', 'a.typeid',
                'epcimg', 'a.epcimg',
                'addtime', 'a.addtime',
			); 
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        parent::populateState('a.epcid', 'asc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.epcid, a.epcname, a.epctid, a.typeid, a.epcimg, a.addtime'));
    	$query->from($db->quoteName('#__epc').' AS a');
        // 联表查询
        $query->select('b.tname');
        $query->join('LEFT', $db->quoteName('#__type').' AS b ON b.typeid = a.typeid');

        
         //接收搜索内容
        $search = $this->getState('filter.search');
        
        if (!empty(trim($search))){
            if (stripos($search, 'epcid:') === 0){
                $query->where('a.epcid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.epcname LIKE '.$search.' or a.epctid LIKE '.$search.')');
            }
        }

        

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        

    	return $query;
    }

    
}
?>
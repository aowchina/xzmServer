<?php
defined('_JEXEC') or die;

class UstyleModelUstyles extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		// 'uscid', 'a.uscid',
          //       'uscname', 'a.uscname',
          //       'ustname', 'a.ustname',
          //  		'ustintime', 'a.ustintime'
                'carid','a.carid',
                'cname','a.cname',
                'vin','a.vin',
                'cimage','a.cimage',
                'price','a.price',
                'issuedate','a.issuedate',
                'addtime', 'a.addtime'
			); 
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        parent::populateState('a.carid', 'asc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.carid, a.vin,a.cname, a.cimage, a.price, a.issuedate,a.addtime'));
    	$query->from($db->quoteName('#__car').' AS a');
        // 联表查询
        $query->select('b.sname');
        $query->join('LEFT', $db->quoteName('#__serial').' AS b ON b.serialid = a.serialid');

        
         //接收搜索内容
        $search = $this->getState('filter.search');
        
        if (!empty(trim($search))){
            if (stripos($search, 'uscid:') === 0){
                $query->where('a.uscid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.cname LIKE '.$search.')');
            }
        }

        

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        

    	return $query;
    }

    
}
?>
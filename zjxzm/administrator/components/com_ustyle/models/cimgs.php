<?php
defined('_JEXEC') or die;

class UstyleModelCimgs extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		// 'uscid', 'a.uscid',
          //       'uscname', 'a.uscname',
          //       'ustname', 'a.ustname',
          //  		'ustintime', 'a.ustintime'
                'imgid','a.imgid',
                'carimage','a.carimage',
                'addtime', 'a.addtime'
			); 
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        parent::populateState('a.imgid', 'asc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.imgid, a.carimage, a.addtime'));
    	$query->from($db->quoteName('#__cimg').' AS a');
        // 联表查询
        $query->select('b.cname');
        $query->join('LEFT', $db->quoteName('#__car').' AS b ON b.carid = a.carid');

        
//         //接收搜索内容
//        $search = $this->getState('filter.search');
//
//        if (!empty(trim($search))){
//            if (stripos($search, 'imgid:') === 0){
//                $query->where('a.imgid = '.(int) substr($search, 3));
//            } else {
//                $search = $db->Quote('%'.$db->escape($search, true).'%');
//                $query->where('(a.uscname LIKE '.$search.')');
//            }
//        }

        

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        

    	return $query;
    }

    
}
?>
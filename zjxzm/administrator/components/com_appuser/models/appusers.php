<?php
defined('_JEXEC') or die;

class AppuserModelAppusers extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'appuid', 'a.appuid',
                'username','a.username',
                'name','a.name',
                'picture','a.picture',
                'type','a.type',
                'tel','a.tel',
                'state','a.state',
                'publish_down','a.publish_down',
                'publish_up','a.publish_up',
                'addtime','a.addtime',
			); 
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        parent::populateState('a.appuid', 'desc');

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
    }



    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)){
            if (stripos($search, 'appuserid:') === 0){
                $query->where('a.appuserid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.tel LIKE '.$search.' or a.name LIKE '.$search.')');
            }
        }

    	$query->select($this->getState('list.select', 'a.appuid, a.name, a.username,a.picture,a.type,a.tel, a.state,a.publish_down,a.publish_up,a.addtime'));
    	$query->from($db->quoteName('#__appuser').' AS a');
//
//         $query->select('e.shopname');
//         $query->join('LEFT', $db->quoteName('#__shop').' AS e ON a.shopid = e.shopid');

//         //多表联查操作
//         $query->select('b.areaname AS pname');
//         $query->join('LEFT', $db->quoteName('#__area').' AS b ON b.id = a.pid');
//
//         $query->select('c.areaname AS cname');
//         $query->join('LEFT', $db->quoteName('#__area').' AS c ON c.id = a.cid');
//
//         $query->select('d.areaname AS qname');
//         $query->join('LEFT', $db->quoteName('#__area').' AS d ON d.id = a.qid');

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        
    	return $query;
    }
}
?>
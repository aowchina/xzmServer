<?php
defined('_JEXEC') or die;

class SellerModelSellers extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'sellerid', 'a.sellerid',
        		'tel', 'a.tel',
        		'name', 'a.name',
        		'is_rz', 'a.is_rz',
        		'type', 'a.type',
        		'lastvisitDate', 'a.lastvisitDate',
        		'shopid', 'a.shopid',
        		'state', 'a.state',
        		'publish_up', 'a.publish_up',
        		'publish_down', 'a.publish_down',

			);
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){
        parent::populateState('a.sellerid', 'desc');

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
    }



    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)){
            if (stripos($search, 'sellerid:') === 0){
                $query->where('a.sellerid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.name LIKE '.$search.' or e.shopname LIKE '.$search.')');
            }
        }

    	$query->select($this->getState('list.select', 'a.sellerid, a.name,a.tel,a.is_rz,a.type,a.shopid,a.lastvisitDate,a.state, a.publish_up, a.publish_down'));
    	$query->from($db->quoteName('#__seller').' AS a');

        // $query->select('e.shopname');
        // $query->join('LEFT', $db->quoteName('#__shop').' AS e ON a.shopid = e.shopid');

        $query->select('f.sname, f.major,f.skill,f.picture ,f.address,f.cardfront,f.cardback,f.cardhand,f.license,f.company,f.number');
        $query->join('LEFT', $db->quoteName('#__sellercert').' AS f ON a.sellerid = f.sellerid');
        /**********************************后加开始by__mo_yu****************************************/
        $query->group('a.sellerid');
        /**********************************后加结束by__mo_yu****************************************/
        //多表联查操作
        // $query->select('b.areaname AS pname');
        // $query->join('LEFT', $db->quoteName('#__area').' AS b ON b.id = f.pid');

        // $query->select('c.areaname AS cname');
        // $query->join('LEFT', $db->quoteName('#__area').' AS c ON c.id = f.cid');

        // $query->select('d.areaname AS qname');
        // $query->join('LEFT', $db->quoteName('#__area').' AS d ON d.id = f.qid');

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        
    	return $query;
    }
}
?>
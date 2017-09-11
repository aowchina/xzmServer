<?php
defined('_JEXEC') or die;

class WalletModelWallets extends JModelList{

	public function __construct($config = array()){
    	if(empty($config['filter_fields'])){
       		$config['filter_fields'] = array(
        		'id', 'a.id',
                'userid', 'a.userid',
				'tid','a.tid',
                'money', 'a.money',
                'addtime','a.addtime',
			); 
		}
       	parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null){

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);

        parent::populateState('a.id', 'desc');
    }

    protected function getListQuery(){
    	$db = $this->getDbo();
    	$query = $db->getQuery(true);

    	$query->select($this->getState('list.select', 'a.id, a.userid,a.tid, a.addtime,a.money'));
    	$query->from($db->quoteName('#__wallet').' AS a');

		//多表联查操作
		$query->select('b.appuid,b.name as aname,b.tel as atel');
        $query->join('LEFT', $db->quoteName('#__appuser').' AS b ON a.userid = b.appuid');

        $query->select('c.sellerid,c.name as sname,c.tel as stel');
        $query->join('LEFT', $db->quoteName('#__seller').' AS c ON a.userid = c.sellerid');
        

		$search = $this->getState('filter.search');
		if (!empty($search)){
			if (stripos($search, 'id:') === 0){
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(b.name LIKE '.$search.')');
			}
		}

        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));
        
    	return $query;
    }
}
?>
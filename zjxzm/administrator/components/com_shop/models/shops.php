<?php
	defined('_JEXEC') or die;
	class ShopModelShops extends JModelList{
		public function __construct($config = array()){
			if (empty($config['filter_fields'])){
				$config['filter_fields'] = array(
						'shopid', 'a.shopid',
						'tel','a.tel',
					'shopname','a.shopname',
						'picture', 'a.picture',
						'number','a.number',
						'rate','a.rate',
					   'addtime','a.addtime',
					   'publish_up','a.publish_up',
					   'publish_down','a.publish_down',
					   'state','a.state',
					);
			}
			parent::__construct($config);
		}

		protected function populateState($ordering = null, $direction = null){
	        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
	        $this->setState('filter.search', $search);

	        parent::populateState('a.shopid', 'desc');
    	}

		protected function getListQuery(){
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			$search = $this->getState('filter.search');
	        if (!empty($search)){
	            if (stripos($search, 'shopid:') === 0){
	                $query->where('a.shopid = '.(int) substr($search, 3));
	            } else {
	                $search = $db->Quote('%'.$db->escape($search, true).'%');
	                $query->where('(a.tel LIKE '.$search.' or a.shopname LIKE '.$search.')');
	            }
	        }


			$query->select(
			        $this->getState('list.select','a.shopid, a.tel,a.shopname,a.picture,a.addtime,a.number,a.rate,a.publish_up,a.publish_down,a.state')
			        );
			$query->from($db->quoteName('#__shop').' AS a');
//			//多表联查操作
//			$query->select('b.shopname');
//			$query->join('LEFT', $db->quoteName('#__seller').' AS b ON b.sellerid = a.sellerid');



	        $orderCol = $this->state->get('list.ordering'); 
	        $orderDirn = $this->state->get('list.direction'); 
	        $query->order($db->escape($orderCol.' '.$orderDirn));
	        
			return $query;
			}
		
	}

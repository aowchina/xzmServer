<?php
    defined('_JEXEC') or die;

    class BillModelBills extends JModelList{

        public function __construct($config = array()){
            if(empty($config['filter_fields'])){
                $config['filter_fields'] = array(
                    'id', 'a.id',
                    'orderid', 'a.orderid',
                    'state', 'a.state',
                    'paytime', 'a.paytime',
                );
            }
            parent::__construct($config);
        }
        //排序
        protected function populateState($ordering = null, $direction = null){
            $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
            $this->setState('filter.search', $search);

            parent::populateState('a.id', 'desc');
        }

        protected function getListQuery(){
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select($this->getState('list.select','a.id, a.orderid, a.state,a.paytime'));
            $query->from($db->quoteName('#__bill').' AS a');

               //接收搜索内容
        $search = $this->getState('filter.search');
        
        if (!empty(trim($search))){
            if (stripos($search, 'id:') === 0){
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.id LIKE '.$search.')');
            }
        }

        //多表联查操作
        $query->select('b.money AS money,b.retime As retime, c.tel AS tel,d.name');
        $query->join('LEFT', $db->quoteName('#__order').' AS b ON a.orderid = b.orderid');
        $query->join('LEFT', $db->quoteName('#__shop').' AS c ON c.shopid = b.shopid');
        $query->join('LEFT', $db->quoteName('#__seller').' AS d ON c.sellerid = d.sellerid');
        $query->where('b.status in(3,4)');

        //排序
        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));

            return $query;
        }
    }
?>
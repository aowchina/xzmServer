<?php
    defined('_JEXEC') or die;

    class PtModelPts extends JModelList{

        public function __construct($config = array()){
            if(empty($config['filter_fields'])){
                $config['filter_fields'] = array(
                    'id', 'a.id',
                    'name', 'a.name',
                    'typeid', 'a.typeid',
                );
            }
            parent::__construct($config);
        }
        //排序
        protected function populateState($ordering = null, $direction = null){
            $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
            $this->setState('filter.search', $search);

            parent::populateState('a.id', 'asc');
        }

        protected function getListQuery(){
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select($this->getState('list.select','a.id,a.name,a.typeid'));
            $query->from($db->quoteName('#__pt').' AS a');
//            $query->where('a.parentid  = 1');

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

         $query->select('b.tname ');
         $query->join('LEFT', $db->quoteName('#__type').' AS b ON b.typeid = a.typeid');
        //排序
        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));

            return $query;
        }
    }
?>
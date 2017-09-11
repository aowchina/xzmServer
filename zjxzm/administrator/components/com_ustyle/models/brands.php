<?php
    defined('_JEXEC') or die;

    class UstyleModelBrands extends JModelList{

        public function __construct($config = array()){
            if(empty($config['filter_fields'])){
                $config['filter_fields'] = array(
                    'brandid', 'a.brandid',
                    'bname', 'a.bname',
                    'blogo', 'a.blogo',
                    'addtime', 'a.addtime',
                    'fname', 'a.fname',
                ); 
            }
            parent::__construct($config);
        }
        //排序
        protected function populateState($ordering = null, $direction = null){
            $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
            $this->setState('filter.search', $search);

            parent::populateState('a.brandid', 'asc');
        }

        protected function getListQuery(){
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            //联表查询
            $query->select($this->getState('list.select','a.brandid, a.bname, a.blogo,a.addtime,a.fname'));
            $query->from($db->quoteName('#__brand').' AS a');

               //接收搜索内容
        $search = $this->getState('filter.search');
        
        if (!empty(trim($search))){
            if (stripos($search, 'brandid:') === 0){
                $query->where('a.brandid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.bname LIKE '.$search.')');
            }
        }
        //排序
        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));

            return $query;
        }
    }
?>
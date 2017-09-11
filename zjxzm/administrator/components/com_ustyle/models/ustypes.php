<?php
    defined('_JEXEC') or die;

    class UstyleModelUstypes extends JModelList{

        public function __construct($config = array()){
            if(empty($config['filter_fields'])){
                $config['filter_fields'] = array(
                    'serialid', 'a.serialid',
                    'sname', 'a.ustname',
                    'simage', 'a.simage',
                    'addtime', 'a.addtime',
                    // 'click', 'a.click'
                ); 
            }
            parent::__construct($config);
        }
        //排序
        protected function populateState($ordering = null, $direction = null){
            $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
            $this->setState('filter.search', $search);

            parent::populateState('a.serialid', 'asc');
        }

        protected function getListQuery(){
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            //联表查询
            $query->select($this->getState('list.select','a.serialid, a.sname, a.simage,a.addtime'));
            $query->from($db->quoteName('#__serial').' AS a');
            // 联表查询
            $query->select('b.bname');
            $query->join('LEFT', $db->quoteName('#__brand').' AS b ON b.brandid = a.brandid');

               //接收搜索内容
        $search = $this->getState('filter.search');
        
        if (!empty(trim($search))){
            if (stripos($search, 'serialid:') === 0){
                $query->where('a.serialid = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.sname LIKE '.$search.')');
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
<?php
    defined('_JEXEC') or die;

    class AdModelAds extends JModelList{

        public function __construct($config = array()){
            if(empty($config['filter_fields'])){
                $config['filter_fields'] = array(
                    'id', 'a.id',
                    'publish_up','a.publish_up',
                    'publish_down','a.publish_down',
                    'state','a.state',
                    'img','a.img',
                    'gid','a.gid',
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
            $query->select($this->getState('list.select','a.id,a.publish_up,a.publish_down,a.state,a.img,gid'));
            $query->from($db->quoteName('#__ad').' AS a');

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

        $query->select('b.name ');
        $query->join('LEFT', $db->quoteName('#__good').' AS b ON b.goodid = a.gid');
        //排序
        $orderCol = $this->state->get('list.ordering'); 
        $orderDirn = $this->state->get('list.direction'); 
        $query->order($db->escape($orderCol.' '.$orderDirn));

            return $query;
        }
    }
?>
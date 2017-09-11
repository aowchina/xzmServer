<?php

defined('_JEXEC') or die;

class ServiceTableService extends JTable{

    public function __construct(&$db){
        parent::__construct('#__service', 'id', $db);
    }

	/**
	 * data保存到db之前的准备
	 */
    public function bind($array, $ignore = ''){
	   return parent::bind($array, $ignore);
    }

	/**
	 * 提交表单时，write db的方法
	 */
    public function store($updateNulls = false){
	   return parent::store($updateNulls);
    }
     public function publish($pks = null, $state = 1, $userId = 0)
     {
         $k = $this->_tbl_key;

         // Sanitize input.
         JArrayHelper::toInteger($pks);
         $userId = (int) $userId;
         $state  = (int) $state;

         // If there are no primary keys set check to see if the instance key is set.
         if (empty($pks))
         {
             if ($this->$k)
             {
                 $pks = array($this->$k);
             }
             // Nothing to set publishing state on, return false.
             else
             {
                 $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

                 return false;
             }
         }
         $query = $this->_db->getQuery(true)
             ->update($this->_db->quoteName($this->_tbl))
             ->set($this->_db->quoteName('state') . ' = ' . (int) $state);

         // Build the WHERE clause for the primary keys.
         $query->where($k . '=' . implode(' OR ' . $k . '=', $pks));

         // Determine if there is checkin support for the table.
 //        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
 //        {
 //            $checkin = false;
 //        }
 //        else
 //        {
 //            $query->where('(checked_out = 0 OR checked_out = ' . (int) $userId . ')');
 //            $checkin = true;
 //        }

         // Update the publishing state for rows with the given primary keys.
         $this->_db->setQuery($query);

         try
         {
             $this->_db->execute();
         }
         catch (RuntimeException $e)
         {
             $this->setError($this->_db->getMessage());

             return false;
         }

         // If checkin is supported and all rows were adjusted, check them in.
 //        if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
 //        {
 //            // Checkin the rows.
 //            foreach ($pks as $pk)
 //            {
 //                $this->checkin($pk);
 //            }
 //        }

         // If the JTable instance value is in the list of primary keys that were set, set the instance.
         if (in_array($this->$k, $pks))
         {
             $this->state = $state;
         }

         $this->setError('');

         return true;
     }
}

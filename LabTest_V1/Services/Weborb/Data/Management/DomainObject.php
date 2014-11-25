<?php

abstract class DomainObject
{
	public $ActiveRecordUID;
	
	public function __construct()
	{		
		$this->ActiveRecordUID = AckMessage::uuid();		
	}
	
	public abstract function getUri();
	public abstract function contains(&$fields);
	 
    public function extractSingleObject()
    {
        return $this;
    }
}
?>
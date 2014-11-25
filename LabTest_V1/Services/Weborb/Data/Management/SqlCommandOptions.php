<?php
class SqlCommandOptions
{
    public /*bool*/ $CreateIfNotFound = false;
    public /*bool*/ $InitializeIfNotFound = false;
    public /*String*/ $Conditions = "";
    public /*List<String>*/$Include = array();
    public /*List<String>*/$Select = array();
    public /*List<String>*/$Group = array();
    public /*Hashtable*/$Fields = array();

    public function IsStrongCondition()
    {
    	if (stripos($this->Conditions, " or ") !== false)
        	return true;
        return false;
    }
}
?>
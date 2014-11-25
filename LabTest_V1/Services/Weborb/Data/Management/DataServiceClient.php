<?php

require_once("IdentityMap.php");

class DataServiceClient
{
    public /*IdentityMap*/ $IdentityMap;
    public /*bool*/ $IsSubscribed;
    public /*List<ActiveQuery>*/ $MonitoredQueries;
    public /*bool*/ $IsOnline = true;

    public function __construct()
    {
    	$this->IdentityMap = new IdentityMap();
        $this->MonitoredQueries = array();        
    }    
}

?>
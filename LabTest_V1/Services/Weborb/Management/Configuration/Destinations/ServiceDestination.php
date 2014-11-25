<?php
class ServiceDestination 
{
	public /*String*/ $ServiceId;
    public /*String*/ $DestinationId;
    public /*boolean*/ $ReadOnly;
    public /*String*/ $Channel = "default channel";
    public /*ArrayList*/ $Roles = array();

    public function ServiceDestination()
    {
    	
    }
    
    public function __construct( /*String*/ $destinationId, /*String*/ $serviceId )
    {
        $this->DestinationId = $destinationId;
        $this->ServiceId = $serviceId;
        $this->ReadOnly = false;
    }
}
?>
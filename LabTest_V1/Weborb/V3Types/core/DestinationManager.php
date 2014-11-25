<?php
require_once(WebOrb . "Util/Cache/Cache.php");
class DestinationManager
{
	private /*Hashtable*/ $destinations;

	public function __construct()
	{
		$this->destinations = array();
	}
	
	public function removeDestination( /*String*/ $id )
    {
        unset($this->destinations[$id]);
    }

    public function addDestination( /*String*/ $id, /*IDestination*/ $destination )
    {
        $this->destinations[$id] =  $destination;
    }

    public /*IDestination*/function getDestination( /*String*/ $id )
    {
        return $this->destinations[$id];
    }

    public /*Hashtable*/function getDestinations()
    {
        return $this->destinations;
    }
}

?>
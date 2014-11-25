<?php
require_once(WebOrb . "V3Types/core/DestinationManager.php");
require_once(WebOrb . "V3Types/core/IAdapter.php");
class DataServices 
{
	private /*Hashtable*/ $adapters = array();
    private /*DestinationManager*/ $destinationManager;
    public $destinations = array();
	private /*IAdapter*/ $defaultAdapter;
	
	public function __construct()
	{
		$this->destinationManager = new DestinationManager();
	}

	public static function addAdapter( /*String*/ $id, /*IAdapter*/ $adapter, /*boolean*/ $isDefault )
	{
		ORBConfig::getInstance()->getDataServices()->_AddAdapter( $id, $adapter, $isDefault );
	}

    public function _AddAdapter( /*String*/ $id, /*IAdapter*/ $adapter, /*boolean*/ $isDefault )
	{
		if( $isDefault )
            $this->defaultAdapter = $adapter;

        $this->adapters[$id] = $adapter;
	}

	public static /*IAdapter*/function getAdapter( /*String*/ $id )
	{
		return ORBConfig::getInstance()->getDataServices()->_GetAdapter( $id );
	}

	public /*IAdapter*/function _GetAdapter( /*String*/ $id )
	{
        if( array_key_exists($id,$this->adapters) )
            return $this->adapters[$id];

        return $this->defaultAdapter;
	}

	public static /*IAdapter*/function getDefaultAdapter()
	{
		return ORBConfig::getInstance()->getDataServices()->_GetDefaultAdapter();
	}
	
	public /*IAdapter*/function _GetDefaultAdapter()
	{
		return $this->defaultAdapter;
	}

    public /*DestinationManager*/function getDestinationManager()
    {
        return $this->destinationManager;
    }
}
?>
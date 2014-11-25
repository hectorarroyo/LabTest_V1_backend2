<?php
require_once(WebOrb . "V3Types/core/IDestination.php");
require_once(WebOrb . "V3Types/core/IServiceHandler.php");

class AbstractDestination implements IDestination
{
	/*Hashtable*/public $properties = array();
	/*HashSet*/private $listeners = array();
    /*IServiceHandler*/public $serviceHandler;
    /*String*/public $name;

    public function setName( /*String*/ $name )
    {
        $this->name = $name;
    }

    public /*String*/ function getName()
    {
        return $this->name;
    }

    public function setProperties( /*Hashtable*/ $properties )
    {
        $this->properties = $properties;
    }

    public /*String*/function getProperty( /*String*/ $name )
    {
        /*Hashtable*/ $props = $this->properties;

        while( true )
        {
            /*int*/ $index = strpos($name, "/");

            if( !$index )
                return /*(String)*/ $props[$name];

            /*String*/ $propName = substr($name,0,$index);//.substring( 0, index );
            $name = substr($name,$index + 1);//.substring( index + 1 );
            $props = /*(Hashtable)*/ $props[$propName];
        }
    }

    public /*IServiceHandler*/function getServiceHandler()
    {
        return $this->serviceHandler;
    }

    public function setServiceHandler($serviceHandler )
    {
        $this->serviceHandler = $serviceHandler;
    }

    public /*boolean*/function setConfigServiceHandler()
    {
        $this->serviceHandler = null;
        return true;
    }

	public /*Hashtable*/function getProperties()
	{
		return $this->properties;
	}
	
  public function messagePublished( /*String*/ $senderId, /*Object*/ $message )
  {
  	foreach ($this->listeners as $key => $val)
  	{
  		/*IMessageEventListener*/ $listener = $val;
  		$listener->messageReceived( $senderId, $message );
  	}
//!!!    for( Iterator iterator = listeners.iterator(); iterator.hasNext(); )
//    {
//      IMessageEventListener listener = (IMessageEventListener) iterator.next();
//      listener.messageReceived( senderId, message );
//    }
  }

  public function addMessageEventListener( /*IMessageEventListener*/ $listener )
  {
    $this->listeners[] = $listener;
  }

  public function removeMessageEventListener( /*IMessageEventListener*/ $listener )
  {
    $k = array_search($listener,$this->listeners);
    unset($this->listeners[$k]);
    $this->listeners = array_values($this->listeners);
  }
}
?>
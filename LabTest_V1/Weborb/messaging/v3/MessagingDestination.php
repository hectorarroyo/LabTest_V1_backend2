<?php
require_once(WebOrb . "V3Types/core/AbstractDestination.php");
require_once(WebOrb . "ORBConstants.php");
require_once(WebOrb . "Util/ObjectFactories.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");

class MessagingDestination extends AbstractDestination
{
	public /*String*/$destinationName;

    public function __construct( /*String*/ $destinationName )
    {
        $this->destinationName = $destinationName;
    }
    
  public /*boolean*/function setConfigServiceHandler()
  {
    if( !array_key_exists(ORBConstants::MESSAGE_SERVICE_HANDLER,$this->getProperties()))
      return true;
    
    $properties = $this->getProperties();
    /*String*/ $handlerClassName = $properties[ORBConstants::MESSAGE_SERVICE_HANDLER];

    try
    {
      /*IServiceHandler*/ $serviceHandler = ObjectFactories::createServiceObject( $handlerClassName );
      $serviceHandler->initialize( $this );
      $this->setServiceHandler( $serviceHandler );
    }
    catch( Exception $exception )
    {
      /*String*/ $error = "Unable to set " . $handlerClassName . " as a message service handler due to an error";

      Log::log( LoggingConstants::ERROR, $error );

      Log::log( LoggingConstants::EXCEPTION, $error, $exception );

      return false;
    }

    return true;
  }    

}
?>
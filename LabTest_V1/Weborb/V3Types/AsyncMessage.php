<?php
require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "V3Types/V3Message.php");
require_once(WebOrb . "V3Types/AckMessage.php");
require_once(WebOrb . "Config/ORBConfig.php");
require_once(WebOrb . "V3Types/core/IDestination.php");

class AsyncMessage extends V3Message
{
  const /*long*/ serialVersionUID = 0;

  public /*V3Message*/function execute( Request $message )
  {
    /*String*/ $dsId = $this->headers["DSId"];
    /*String*/ $woId = $this->headers["WebORBClientId"];
    /*IDestination*/ $destObj = ORBConfig::getInstance()->getDataServices()->getDestinationManager()->getDestination( $this->destination );

    if( $destObj == null )
    {
      /*String*/ $error = "Unknown destination - " . $destination . ". Make sure the destination is properly configured.";

      Log::log( LoggingConstants::ERROR, $error );

      return new ErrMessage( $this->messageId, new Exception( $error ) );
    }

    /*Object[]*/ $bodyParts = $this->body->body;

    if( $bodyParts != null && count($bodyParts) > 0 )
    {
      for( $i = 0; $i < count($bodyParts); $i++ )
        $this->body->body[ $i ] = $bodyParts[ $i ]->defaultAdapt();

      $destObj->messagePublished( $woId, $bodyParts[ 0 ] );
      $destObj->getServiceHandler()->addMessage( $this->headers, $this );
    }

    return new AckMessage( $this->messageId, $this->clientId, null, array() );
  }
}
?>
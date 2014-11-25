<?php
require_once(WebOrb . "V3Types/core/IServiceHandler.php");
require_once(WebOrb . "messaging/v3/MemoryStoragePolicy.php");
require_once(WebOrb . "messaging/v3/Subscriber.php");
require_once(WebOrb . "messaging/v3/PendingMessage.php");
require_once(WebOrb . "Util/Cache/Cache.php");
require_once(WebOrb . "Util/ObjectFactories.php");
require_once(WebOrb . "ORBHttpHandler.php");

class MessagingServiceHandler implements IServiceHandler
{
  private /*IMessageStoragePolicy*/ $storagePolicy = null;

  public function initialize( /*IDestination*/ $destination )
  {
    $props = $destination->getProperties();
	/*String*/ $storagePolicy = $props[ORBConstants::MESSAGE_STORAGE_POLICY];
  }
  public function handleSubscribe( /*Subscriber*/ $subscriber )
  {
  }

  public function handleUnsubscribe( /*Subscriber*/ $subscriber )
  {
  }
  
  public /*ArrayList*/function getMessages( /*Subscriber*/ $subscriber )
  {
  	$returnMessage = $this->getStoragePolicy()->getMessages( $subscriber );
  	Cache::put("storagePolicy", $this->storagePolicy);
    return $returnMessage;
  }

  public function addMessage( /*Hashtable*/ $properties, /*Object*/ $message )
  {
    $this->getStoragePolicy()->addMessage( new PendingMessage($properties, $message ) );
    Cache::put("storagePolicy", $this->storagePolicy);    
  }

  public /*IMessageStoragePolicy*/function getStoragePolicy()
  {
    if( $this->storagePolicy != null )
      return $this->storagePolicy;
    elseif (Cache::get("storagePolicy")!=null && Cache::get("storagePolicy") !== false)
    {
    	$this->storagePolicy = Cache::get("storagePolicy");
    }
    else
      $this->setDefaultStoragePolicy();

    return $this->storagePolicy;
  }

  public function setStoragePolicy( /*String*/ $storagePolicyClass )
  {
    if( $storagePolicyClass == null )
      return;

    try
    {
      /*IMessageStoragePolicy*/ $storagePolicy = ObjectFactories::createServiceObject($storagePolicyClass);
      $this->storagePolicy = $storagePolicy;
    }
    catch( Exception $exception )
    {
      Log::log( LoggingConstants::EXCEPTION, "unable to create message storage policy object - " . $storagePolicyClass ." ". $exception );

      $this->setDefaultStoragePolicy();
    }
  }

  public function setDefaultStoragePolicy()
  {
    $this->storagePolicy = new MemoryStoragePolicy();
  }
}
?>

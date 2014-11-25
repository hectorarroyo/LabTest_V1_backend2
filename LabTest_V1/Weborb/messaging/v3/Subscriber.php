<?php
require_once(WebOrb . "V3Types/V3Message.php");
require_once(WebOrb . "V3Types/AsyncMessage.php");
require_once(WebOrb . "V3Types/BodyHolder.php");
require_once(WebOrb . "Util/Cache/Cache.php");

class Subscriber 
{
	private /*String*/ $dsId;	
	private /*String*/ $clientId;
	private /*String*/ $subtopic;
	private /*long*/ $lastRequestTime;
  	private /*String*/ $selectorName;
	private /*IMessageSelector*/ $selector;
	private /*IDestination*/ $destination;
	private /*Hashtable*/ $properties = array();	
	
	public function __construct( /*String*/ $selectorName, /*IDestination*/ $destination )
	{
		$this->destination = $destination;
    	$this->lastRequestTime = microtime(true);
    	Cache::put("lastRequestTime" . $this->dsId . "_" . $this->clientId, microtime(true));
    	$this->selectorName = $selectorName;

    	if( $selectorName != null )
      		$this->createSelector();
	}
	
	public /*Object*/function getProperty( /*String*/ $key )
  	{
    	return $this->properties[$key];
  	}
  
	public /*void*/function setProperty( /*String*/ $key, /*Object*/ $value )
  	{
    	$this->properties[$key] = $value;
  	}
  	
	public function removeProperty( /*String*/ $key )
  	{
    	unset($this->properties[$key]);
  	}
  	
	public /*String*/function getStringSelector()
  	{
    	if( $this->selector == null && $this->selectorName != null )
      		return $this->selectorName;

    	return null;
  	}
  	
	public function setDSId( /*String*/ $dsId )
  	{
    	$this->dsId = $dsId;
  	}
	
	public /*String*/function getDSId()
	{
		return $this->dsId;
	}

	public function setClientId( /*String*/ $clientId )
  	{
    	$this->clientId = $clientId;
  	}
	
	public /*String*/function getClientId()
	{			
		return $this->clientId;
	}
	
	public /*String*/function getSubtopic()
	{
		return $this->subtopic;
	}
	
	public function setSubtopic( /*String*/ $subtopic )
	{
		$this->subtopic = $subtopic;
	}
	
	public /*IDestination*/function getDestination()
  	{
    	return $this->destination;
  	}
	
	public /*IMessageSelector*/function getSelector()
	{
		return $this->selector;
	}
	
	public function setSelector( /*IMessageSelector*/ $selector )
	{			
		$this->selector = $selector;
	}
	
	public function setLastRequestTime( /*long*/ $time )
  	{
    	$this->lastRequestTime = $time;
    	Cache::put("lastRequestTime" . $this->dsId . "_" . $this->clientId, $time);
  	}

  	public /*long*/function getLastRequestTime()
  	{
  		$this->lastRequestTime = Cache::get("lastRequestTime" . $this->dsId . "_" . $this->clientId);
   		return $this->lastRequestTime;
  	}
	
	public /*ArrayList*/function filterMessages( /*ArrayList*/ $messages )
	{
		/*ArrayList*/ $returnMessages = array();
		/*V3Message*/ $v3message;
		for( $i = 0; $i < count($messages); $i++ )
    	{
      		/*Object*/ $message = $messages[$i];

      		if( $this->selector != null )
        		if( $messages[$i] instanceof V3Message )
          			$message = $this->selector->processClientMessage( $message );
        		else
          			$message = $this->selector->processServerMessage( $message );

      		if( $message instanceof V3Message )
      		{
        		$v3message = $message;

        		if( $this->selector != null )
          			$v3message->clientId = $selector->getClientId();
      		}
      		else
      		{
        		$v3message = $this->createAsyncMessage( $message, $this->selector->getClientId() );
      		}
			/*String*/ $messageSubtopic = $v3message->headers["DSSubtopic"];
		    if( $this->isMessageInSubtopic( $messageSubtopic ) )
		    	array_push( $returnMessages, $v3message );
    	}
		if(count($returnMessages)==1)
			return $returnMessages[0];
    	return $returnMessages;
	}
	
	private /*boolean*/function isMessageInSubtopic( /*String*/ $messageSubtopic )
  	{  
	  if( $messageSubtopic == null 
		|| $this->destination->getProperty( ORBConstants::SERVER . "/" . ORBConstants::ALLOW_SUBTOPICS ) == null
		|| $this->destination->getProperty( ORBConstants::SERVER . "/" . ORBConstants::ALLOW_SUBTOPICS ) == "false"
		|| $this->subtopic == null
		|| $this->subtopic == ""
		|| $this->subtopic == "*"
		|| $messageSubtopic == ""
		|| $messageSubtopic == "*"
		)
		  return true;
	 	
	  /*String*/ $separator =  $this->destination->getProperty( ORBConstants::SERVER . "/" . ORBConstants::SUBTOPIC_SEPARATOR );		 	  	
	  /*String[]*/ $subtopicTokens = explode($separator, $this->subtopic);
	  /*String[]*/ $messageSubtopicTokens = explode($separator, $messageSubtopic);			  
//	 	var_dump($subtopicTokens); echo "__"; var_dump($messageSubtopicTokens);exit;
	  if( count($subtopicTokens) < count($messageSubtopicTokens) 
		  && !( $subtopicTokens[ 0 ] == "*" || $subtopicTokens[ count($subtopicTokens) - 1 ] == "*" ) )
		  return false;
	  
	  try
	  {
		  if( $subtopicTokens[ 0 ] == "*" || $messageSubtopicTokens[ 0 ] == "*" )
			  for( $i = 0; $i < count($subtopicTokens); $i++ )
				  if( $subtopicTokens[ count($subtopicTokens) - $i - 1 ] == "*" 
					|| $messageSubtopicTokens[ count($messageSubtopicTokens) - $i - 1  ] == "*"  
					|| $subtopicTokens[ count($subtopicTokens) - $i - 1 ] == $messageSubtopicTokens[ count($messageSubtopicTokens) - $i - 1 ]  )
					  continue;
				  else
					  return false;
		  else
			  for( $i = 0; $i < count($subtopicTokens); $i++ )
				  if( $subtopicTokens[ $i ] == "*" 
					|| $messageSubtopicTokens[ $i ] == "*" 
					|| $subtopicTokens[ $i ] == $messageSubtopicTokens[ $i ] )
					  continue;
				  else
					  return false;
		  
		  return true;
	  }
	  catch( IndexOutOfBoundsException $e )
	  {
		  if( ( strpos($messageSubtopic, $separator == null ? "*." : "*" . $separator ) == 0
				  && count($subtopicTokens) >= count($messageSubtopicTokens) )
			|| ( strrpos($this->subtopic, $separator == null ? ".*" : $separator . "*") == ( strlen($this->subtopic) - strlen($separator == null ? ".*" : $separator . "*") - 1 )
				  && count($subtopicTokens) <= count($messageSubtopicTokens) 
				) )
			  return true;
		  
		  return false;
	  }	  
  	}
	
	private /*String*/function getSplitSeparator( /*String*/ $separator )
 	{
	  if( $separator == null )
		  $separator = "\\.";
	 
	  if( $separator == "."  || $separator = "?" || $separator == "^" 
			  || $separator == "[" || $separator == "]" 
			  || $separator == "(" || $separator == ")" 
			  || $separator == "{" || $separator == "}" 
			  || $separator == "-" || $separator == "&" 
			  || $separator == "|" || $separator == "+" )
		  $separator = "\\\\" . $separator;
	  
	  if( $separator = "\\" )
		  $separator = "\\\\"; 
	  
	  return $separator;
  	}
	
	private /*AsyncMessage*/function createAsyncMessage( /*Object*/ $body, /*String*/ $clientId )
	{
		if( $clientId == null )
			$clientId = $this->getClientId();
		
		/*AsyncMessage*/ $message = new AsyncMessage();
		$message->body = new BodyHolder();
		$message->body->body = $body;
		$message->headers = array();
		$message->headers["DSId"] = $this->getDSId();
		$message->clientId = $clientId;
		$message->timestamp = time();
		
		return $message;
	}
	private function createSelector()
  	{
    	/*IMessageSelector*/ $selector = null;

    	if( $this->selectorName != null && strlen(trim($this->selectorName)) > 0 )
    	{
      		$this->selectorName = ServiceRegistry::getMapping( $this->selectorName );

      		try
      		{
        		$selector = ObjectFactories::createServiceObject( $this->selectorName );

        		if( $selector != null )
          			$selector->setClientId( $this->clientId );
      		}
      		catch( Exception $exception )
      		{
        		Log::log( LoggingConstants::ERROR, "unable to create message selector object" );
         		Log.log( LoggingConstants::ERROR, "will treat the selector as a query - " . $this->selectorName );
      		}
    	}
  	}
}
?>
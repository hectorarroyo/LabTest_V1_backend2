<?php
require_once 'Responder.php';
require_once 'DefaultResponder.php';
require_once 'Fault.php';

require_once WebOrb . 'Reader/MessageDataReader.php';
require_once WebOrb . 'Message/Header.php';
require_once WebOrb . 'Reader/ConcreteObject.php';
require_once WebOrb . 'V3Types/ReqMessage.php';
require_once WebOrb . 'V3Types/BodyHolder.php';
require_once WebOrb . 'Message/Body.php';
require_once WebOrb . 'Message/Request.php';
require_once WebOrb . 'Writer/AmfV3Formatter.php';
require_once WebOrb . 'Writer/MessageWriter.php';

class WeborbClient
{
  private /*String*/ $gatewayURL;
  private /*String*/ $destination = "GenericDestination";

  public function __construct(/*String*/ $gatewayURL, /*String*/ $destination = "GenericDestination" )
  {
    $this->gatewayURL = $gatewayURL;
    $this->destination = $destination;
  }
  
  public function invoke()
  {
  	$arg = func_get_args();
  	$countArgs = func_num_args();
  	if( $countArgs == 3 )
  	{
  		$this->invoke1($arg[0], $arg[1], $arg[2]);
  	}
  	elseif( $countArgs == 4 )
  	{
  		$this->invoke2($arg[0], $arg[1], $arg[2], $arg[3]);
  	}
  	elseif ($countArgs == 5)
  	{
  		$this->invoke3($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
  	}
  	
  }

  public function invoke1( /*String*/ $methodName, /*Object[]*/ $args, /*Responder*/ $responder ) 
  {
    $this->invoke2( null, $methodName, $args, $responder );
  }

  public function invoke2( /*String*/ $className, /*String*/ $methodName, /*Object[]*/ $args, /*Responder*/ $responder ) 
  {
    $this->invoke3( $className, $methodName, $args, $responder, new AsyncStreamSetInfo() );
  }

  public function invoke3( /*String*/ $className, /*String*/ $methodName, /*Object[]*/ $args, /*Responder*/ $responder, AsyncStreamSetInfo $asyncStreamSetInfo )
  {

   /* byte[]*/ $requestBytes = $this->createRequest( $className, $methodName, $args, null );
    
	$postMethod = $this->do_post_request($this->gatewayURL, $requestBytes);

	
    if( $responder != null )
      $asyncStreamSetInfo->responder = $responder;
    else
      $asyncStreamSetInfo->responder = new DefaultResponder();


    $this->processAMFResponse( $postMethod, $asyncStreamSetInfo );
  }

  private function processAMFResponse( /*InputStream*/ $streamResponse, AsyncStreamSetInfo $asyncStreamSetInfo )
  {
    /*MessageDataReader*/ $parser = new MessageDataReader();
    /*Message*/ $responseObject = $parser->readMessage( $streamResponse );
    /*Object[]*/ $responseData = $responseObject->getRequestBodyData();
    /*V3Message*/ $v3 = $responseData[ 0 ]->defaultAdapt();

    if( $v3->isError() )
    {
      /*ErrMessage*/ $errorMessage = $v3;
      /*Fault*/ $fault = new Fault( $errorMessage->faultString, $errorMessage->faultDetail );
      $asyncStreamSetInfo->responder->errorHandler( $fault );
    }
    else
    {
      /*Object[]*/ $returnValue = $v3->body->body;
      /*Object[]*/ $adaptedObject = array();

      for( $i = 0; $i < count($returnValue); $i++ )
        $adaptedObject[ $i ] = $returnValue[ $i ]->defaultAdapt();
		var_dump($adaptedObject);
		Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
      $asyncStreamSetInfo->responder->responseHandler( $adaptedObject );
    }

    
  }
  
  public /*byte[]*/ function createRequest()
  {
  	$arg = func_get_args();
  	$countArgs = func_num_args();
  	if( $countArgs == 2 )
  		return $this->createRequest1($arg[0], $arg[1]);
  	else//if ( $countArgs == 4 )
  	{
  		return $this->createRequest2($arg[0], $arg[1], $arg[2], $arg[3]);
  	}
  	
  }

  public /*byte[]*/ function createRequest1( /*String*/ $methodName, /*Object[]*/ $args )
  {
    return $this->createRequest2( null, $methodName, $args, array() );
  }

  public /*byte[]*/ function createRequest2( /*String*/ $className, /*String*/ $methodName, /*Object[]*/ $args, /*Hashtable*/ $headers ) 
  {
    /*Header[]*/ $headersArray = null;

    if( count($headers) != 0 )
    {
      $headersArray = array();
      $i = 0;

      foreach ( $headers as $headerName => $header )
      {
        $headersArray[ $i ] = new Header( $headerName, false, -1, new ConcreteObject( $header ) );
        $i++;
      }
    }

    /*Body[]*/ $bodiesArray = array();
    /*ReqMessage*/ $bodyMessage = new ReqMessage();
    $bodyMessage->body = new BodyHolder();
    $bodyMessage->body->body = $args;
    $bodyMessage->destination = $this->destination;

    if( $className != null )
      $bodyMessage->source = $className;

    $bodyMessage->operation = $methodName;
    $bodiesArray[ 0 ] = new Body( ".", ".", -1, null );

    /*Message*/ $request = new Request( 3, $headersArray, $bodiesArray );
    $request->setResponseBodyPart($bodyMessage);//setResponseBodyData( bodyMessage );
	
    /*AmfV3Formatter*/ $formatter = new AmfV3Formatter();
    MessageWriter::writeObject( $request, $formatter );
    /*byte[]*/ $requestBytes = $formatter->getBytes();
//    var_dump($requestBytes);
//
//		Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
      
    $formatter->cleanup();

    return $requestBytes;
  }
  
  public function do_post_request($url, $strData)
  {

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => "Content-type: application/x-amf",
            'content' => $strData
        )
    );

    $context  = stream_context_create($opts);
	$result = file_get_contents($url, false, $context);

    return  $result;
 }
}

class AsyncStreamSetInfo
{
  public /*byte[]*/ $requestBytes;
  public /*Responder*/ $responder;
}
?>
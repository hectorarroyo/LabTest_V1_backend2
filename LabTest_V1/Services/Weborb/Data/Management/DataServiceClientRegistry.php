<?php
require_once("IdentityMap.php");
require_once("DataServiceClient.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

class DataServiceClientRegistry
{
    const /*string*/ CLIENT_URI_KEY = "wdm-client-uri";

    private static /*Dictionary<String, DataServiceClient>*/ $items = array();

    public static /*IdentityMap*/ function getIdentityMap( /*String*/ $clientUri = NULL )
    {        
        if( is_null( $clientUri ) )
        {
        	if( !isset( $_GET["clientid"] ) )        	
            	return NULL;
            else
            {
            	$CurrentClientId = $_GET["clientid"];
            	return self::getIdentityMap( $CurrentClientId );
            }
        }
        
        /*DataServiceClient*/ $client = self::getClient( $clientUri );
        
    	if( is_null( $client ) )
        	return NULL;
                  
        return $client->IdentityMap;
    }

    public static /*DataServiceClient*/ function &getClient( /*String*/ $clientId = NULL )
    {
	
    	if(count(self::$items) == 0 && isset($_SESSION["queryId"]))
    	{
    		self::$items = unserialize($_SESSION["queryId"]);
    	}
        if( is_null( $clientId ) )
        {
        	if( !isset( $_GET["clientid"] ) )        	
            	return NULL;
            else
            {
            	$CurrentClientId = $_GET["clientid"]; 
            	return self::getClient( $CurrentClientId );
            }
        }

        if( !array_key_exists( $clientId, self::$items ) )
        {        	
            /*DataServiceClient*/ $dataServiceClient = new DataServiceClient();        
            self::$items[ $clientId ] = $dataServiceClient; 
            $_SESSION["queryId"] = serialize(self::$items);                    
        }
        
        return self::$items[ $clientId ];
    }
    
    public static function save()
    {
    	$_SESSION["queryId"] = serialize(self::$items);
    }

    public static /*bool*/ function IsSubscribed( /*String*/ $clientId = NULL )
    {
    	if( is_null( $clientId ) )
        {
        	if( !isset( $_GET["clientid"] ) )        	
            	return NULL;
            else
            {
            	$CurrentClientId = $_GET["clientid"];
            	return self::IsSubscribed( $CurrentClientId );
            }
        }
        
        return getClient( $clientId )->IsSubscribed;
    }

    public static /*void*/ function subscribe( /*String*/ $clientUri )
    {        
    }

    public static /*void*/ function unsubscribe( /*String*/ $clientUri )
    {     
    }
   
    public static /*void*/ function process( /*List<AffectedObject>*/ $affectedObjects )
    {      
    }
}

?>
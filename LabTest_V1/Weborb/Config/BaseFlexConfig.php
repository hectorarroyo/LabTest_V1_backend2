<?php
require_once(WebOrb . "Util/Logging/LoggingConstants.php");

abstract class BaseFlexConfig
{
	 public $orbConfig;
     public /*String*/ $basePath;
     public abstract /*String*/function getConfigFileName();
     public abstract /*String*/function getDefaultServiceHandlerName();
     public abstract /*void*/function preConfig();
     public abstract /*void*/function postConfig();
     public abstract /*IDestination*/function processDestination( ORBConfig $config, /*String*/ $destinationId, /*Element*/ $xmlElement );

     public /*void*/function Configure( /*String*/ $basePath, ORBConfig $orbConfig ) //throws Exception
     {
         $this->orbConfig = $orbConfig;
         $this->basePath = $basePath;
         $this->preConfig();
         /*Document*/ $configDoc = null;

        $dom = new DomDocument();
        $dom->load($orbConfig->getFlexConfigPath() . $this->getConfigFileName() );

         $root = $dom->documentElement;

         /*String*/ $serviceHandlerClassName = $root->getAttribute( "class" );

         //if( $serviceHandlerClassName == null || strlen($serviceHandlerClassName) == 0 )
             $serviceHandlerClassName = $this->getDefaultServiceHandlerName();

         /*IServiceHandler*/ $serviceHandler = null;

	     if( $serviceHandlerClassName != null )
			 try
			 {
				 $serviceHandler = /*(IServiceHandler)*/ObjectFactories::createServiceObject( $serviceHandlerClassName );
			 }
			 catch( Exception $e)
			 {
				$serviceHandler = null;
			 }

         /*DataServices*/ $dataServices = $orbConfig->getDataServices();
         /*List*/ $adapters = $root->getElementsByTagName( "adapters" );

         if( $adapters != null && $adapters->length > 0 )
         {
           /*Element*/ $adaptersNode = /*(Element)*/$adapters->item(0);
           /*List*/ $adaptersDefNodes = $adaptersNode->getElementsByTagName( "adapter-definition" );

           for($i = 0; $i < count($adaptersDefNodes); $i++ )
           {

               /*Element*/ $adapterDefinition = /*(Element)*/$adaptersDefNodes->item($i);
               /*String*/ $id = $adapterDefinition->getAttribute( "id" );
               /*String*/ $type = $adapterDefinition->getAttribute( "class" );

               if( $type == null )
                   $type = $adapterDefinition->getAttribute( "type" );

               if( $type == null || strlen(trim($type)) == 0 )
                   continue;

               /*String*/ $defaultAdapterStr = $adapterDefinition->getAttribute( "default" );
               /*boolean*/ $defaultAdapter = $defaultAdapterStr != null && strtolower($defaultAdapterStr)=="true";

               try
               {
                   /*IAdapter*/ $adapter = /*(IAdapter)*/ObjectFactories::createServiceObject( $type );
                   $dataServices->_AddAdapter( $id, $adapter, $defaultAdapter );
               }
               catch( Exception $e )
               {
                   Log::log( LoggingConstants::ERROR, "unable to load service adapter " . $type );
               }
           }
         }

     	 /*List*/ $destinationNodes = $root->getElementsByTagName( "destination" );

         for( $i = 0; $i < $destinationNodes->length; $i++ )
         {
        	 /*Element*/ $destElement = $destinationNodes->item( $i );
             /*String*/ $destinationId = $destElement->getAttribute( "id" );
             /*IDestination*/ $destination = $this->processDestination( $orbConfig, $destinationId, $destElement );
             $destination->setName( $destinationId );
             /*Element*/ $props = $destElement->getElementsByTagName( "properties" )->item( 0 );
             $destination->setProperties( $this->parseProperties( $props ) );
             $destination->setConfigServiceHandler();

             if( $destination->getServiceHandler() == null )
            	 $destination->setServiceHandler( $serviceHandler );

             $dataServices->getDestinationManager()->addDestination( $destinationId, $destination );
         }
         $this->postConfig();
     }

     private /*Hashtable*/function parseProperties( /*Element*/ $propertiesElement )
     {
     	 /*Hashtable*/ $props = array();
     	 /*List*/ $propsNodes = $propertiesElement->childNodes;
		 for($i = 0;$i<$propsNodes->length; $i++)
		 {
		 	/*Object*/ $xmlNode = $propsNodes->item($i);

		 	if (@$xmlNode->childNodes->length > 1)
		 	{
		 		$props[$xmlNode->nodeName] = $this->parseProperties($xmlNode);
		 	}
		 	else
		 	{
		 		if(trim($xmlNode->textContent) == null) continue;
		 		$props[$xmlNode->nodeName] = $xmlNode->textContent;
		 	}

		 }

		 return $props;
    }
}
?>
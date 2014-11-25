<?php
require_once(WebOrb . "../Services/Weborb/Management/ServiceBrowser/ServiceScanner.php");
require_once WebOrb . 'Registry/MonitoredClass.php';
require_once WebOrb . 'DCA/Util/MethodProperty.php';

class DCAUtils 
{

	public static /*ArrayList*/ function getPackageClasses( /*String*/ $pckgname )
	{
		$list = array();
		$WebOrbServicesPath = WebOrb . "../Services/";
		$dir = $WebOrbServicesPath . str_replace(".", "/", $pckgname);
		$dirList = scandir($dir);
		foreach ($dirList as $dirElement)
		{
			if ($dirElement == ".svn" || $dirElement == '.' || $dirElement == '..')
				continue;
				
			if(strpos($dirElement, '.php') !== false)
			{
				$serviceName = $pckgname . "." . substr($dirElement, 0, strlen($dirElement)-4);
				try
				{								
					/*Class*/ $class = TypeLoader::loadType( $serviceName );
					
					if( $class != null )
					{
						$methods = $class->getMethods();
						foreach ($methods as $method)
						{
							if($method->isPublic())
							{							
								$method = new MethodProperty($serviceName, $method->getName());
								$list[] = $method;
							}
						}
										
						continue;
					}
				}
				catch( Exception $e )
				{
					// this is not a class
				}
			}
			else 
			{
				$methodList = DCAUtils::getPackageClasses($pckgname . "." . $dirElement);
				foreach ($methodList as $method)
					$list[] = $method;
			}
		}
		return $list;
	}
//	
	public static /*String*/function checkURL( /*String*/ $url )
	{
		if( strrpos($url, "/" ) == strlen($url)-1 )
			$url .= "weborb.php";
		
		if( strrpos($url, "weborb.php" ) != strlen($url)-12 )
			$url .= "/weborb.php";
		
		return $url;
	}
	
	public static /*String*/function getTypeName( /*Class*/ /*$class*/ )
	{
		return "Object";
		
		$serviceScanner =  new ServiceScanner();
		/*String*/ $name = $serviceScanner->getType( $class );
		if( $name != null )
			return $name;
				
		return "Object";
	}
}
?>
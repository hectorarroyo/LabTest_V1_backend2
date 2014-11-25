<?php

require_once(WebOrb . "Util/Cache/Lite.php");

class Cache
{
	static private $instance;

	static public function get( $name, $lifeTime = 3600 )
    {

		return self::getCache($lifeTime)->get($name);
	}

	static public function put( $name, $obj )
	{
		self::getCache()->save( $obj, $name);
	}

	static private function getCache($lifeTime = 3600)
	{
		 if( !self::$instance )
		 {
			// Set a few options
			$options = array
			(
				'cacheDir' => WebOrbCache,
				'lifeTime' => $lifeTime
			);

			// Create a Cache_Lite object
			self::$instance = new Cache_Lite($options);
		 }

		 return self::$instance;
	}
}

?>
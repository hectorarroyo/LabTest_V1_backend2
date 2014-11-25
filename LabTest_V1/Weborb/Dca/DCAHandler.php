<?php
	if (!defined("WebOrb"))
	{
	    define("WebOrb", dirname(__FILE__) . DIRECTORY_SEPARATOR);
	}
	
	if (!defined("WebOrbCache"))
	{
	    define("WebOrbCache", dirname(__FILE__) . DIRECTORY_SEPARATOR . "PollCache" . DIRECTORY_SEPARATOR);
	}
	
	$object = unserialize($_GET['object']);
	$weborbDCA = new WebORBDataCollectionAgent();
	
?>
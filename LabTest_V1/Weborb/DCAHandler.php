<?php
	if (!defined("WebOrb"))
	{
	    define("WebOrb", dirname(__FILE__) . DIRECTORY_SEPARATOR);
	}
	
	if (!defined("WebOrbCache"))
	{
	    define("WebOrbCache", dirname(__FILE__) . DIRECTORY_SEPARATOR . "PollCache" . DIRECTORY_SEPARATOR);
	}
	
	require_once WebOrb . "DCA/WebORBDataCollectionAgent.php";
	require_once WebOrb . 'Registry/MonitoredClass.php';
	require_once(WebOrb . 'Util/Logging/Log.php');
	require_once(WebOrb . "Util/Logging/LoggingConstants.php");
	require_once WebOrb . 'Config/ORBConfig.php';
	ob_start();
	$config = ORBConfig::getInstance();
	if (true || isset($_POST['action']) && $_POST['action'] == 'sendDCA')
	{
		WebORBDataCollectionAgent::startup('http://localhost/weborb/PHP/weborb.php');
		$weborbDCA = WebORBDataCollectionAgent::getInstance();
		$weborbDCA->sendDCAInformationToRBI();
		Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
		exit;
	}	
	$monitoredClass = unserialize($_POST['object']);
	WebORBDataCollectionAgent::startup('http://192.168.1.102/weborb/PHP/weborb.php');
	$weborbDCA = WebORBDataCollectionAgent::getInstance();
//	$weborbDCA->sendDCAInformationToRBI();
	$weborbDCA->getMonitoredRIsFromRBIServer();
	
//	var_dump($monitoredClass);
	for($i = 0; $i<MonitoredClass::MAX_CACHE; $i++)
	{
		if($monitoredClass->setCurrentMethodNumber($i))
			$weborbDCA->update($monitoredClass);
	}
	Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
	ob_end_flush();
?>
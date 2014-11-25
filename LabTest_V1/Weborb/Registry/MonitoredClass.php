<?php
require_once(WebOrb . 'Util/Logging/Log.php');
require_once(WebOrb . "Util/Logging/LoggingConstants.php");

class MonitoredClass {
	
	const MAX_CACHE = 1;
	const URL = "http://localhost/weborb/PHP/Weborb/DCAHandler.php";
	
	private $methodName;
	private $fullName;
	private $invocationTimes = array();
	private $midleTimeInvocation;
	private $results = array();
	private $numberOfInvoke;
	private $args = array();
	private $timestaps = array();
	private $currentMethodNumber = 0;
	private /*RaflactionClass*/$class;
	private $fullMethodName;
	
	private $sendCount;
	
	public function __construct(ReflectionClass $class, $fullName, $methodName)
	{
		
		$this->class = new ReflectionClass($class->getName());
		$this->fullName = $fullName;
		$this->methodName = $methodName;
		$this->numberOfInvoke = 0;
		$this->sendCount = 0;
		$this->fullMethodName = $methodName . "( " . count($class->getMethod($methodName)->getParameters()) . " arguments )";
	}
	
	public function addInvoke($invokeTime, $args, $result)
	{
		$this->numberOfInvoke++;
		$noi = ($this->numberOfInvoke - self::MAX_CACHE * $this->sendCount) - 1;
		$this->invocationTimes[$noi] = $invokeTime;
		$this->args[$noi] = $args;
		$this->results[$noi] = $result;
		$this->timestaps[$noi] = time()*1000;
		$this->midleTimeInvocation = ($this->midleTimeInvocation*($this->numberOfInvoke-1)+$invokeTime)/$this->numberOfInvoke;
		
		if($noi+1 >= self::MAX_CACHE )
		{
			$this->sendToDCA();
		}
	}
	
	private function sendToDCA()
	{
		$this->sendCount++;
		$data = clone($this);
		$request = serialize($data);
		$this->doRequest(self::URL, $request);
		
		$this->args = array();
		$this->results = array();
		$this->timestaps = array();
		$this->currentMethodNumber = 0;
	}
	
	private function doRequest($url, $request)
	{
	    $ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "object=" . $request);
		curl_exec($ch);
		curl_close($ch);
	}
	
	public /*bool*/function setCurrentMethodNumber(/*int*/ $i)
	{
		if ($i <= ($this->numberOfInvoke - self::MAX_CACHE * $this->sendCount))
		{
			$this->currentMethodNumber = $i;
			return true;
		}
		
		return false; 
	}
	
	public /*int*/ function getCurrentMethodNumber()
	{
		return $this->currentMethodNumber;
	}
	
	public function getTimestap($methodNumber = "")
	{
		if($methodNumber == "")
		{
			$methodNumber = $this->getCurrentMethodNumber();
		}
		
		return $this->timestaps[$methodNumber];
	}
	
	public function getResult($methodNumber = "")
	{
		if($methodNumber == "")
		{
			$methodNumber = $this->getCurrentMethodNumber();
		}
		
		return $this->results[$methodNumber];
	}
	
	public function getArgs($methodNumber = "")
	{
		if($methodNumber == "")
		{
			$methodNumber = $this->getCurrentMethodNumber();
		}
		
		return $this->args[$methodNumber];
	}
	
	public function getInvokeTime($methodNumber = "")
	{
		if($methodNumber == "")
		{
			$methodNumber = $this->getCurrentMethodNumber();
		}
		
		return $this->invocationTimes[$methodNumber]*10000;
	}
	
	public function getNamespace()
	{
		$namespace = $this->fullName;
		if (strpos($namespace, '.' ) !== false)
		{
			$namespace = substr($namespace, 0, strrpos($namespace, '.'));
			$namespace = substr($namespace, 0, strrpos($namespace, '.'));
		}
		return $namespace;		
	}
	
	public function getClassName()
	{
		$class = $this->fullName;
		if (strpos($class, '.' ) !== false)
		{
			$class = substr($class, 0, strrpos($class, '.'));
			if (strpos($class, '.' ) !== false)
				$class = substr($class, strrpos($class, '.') + 1, strlen($class)-1);
		}
		return $class;
	}
	
	public function getMethodName()
	{
		return $this->methodName;
	}
	
	public function getFullMethodName()
	{
		return $this->fullMethodName;
	}
}

?>

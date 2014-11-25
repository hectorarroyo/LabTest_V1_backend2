<?php
class MethodProperty {
	
	private $serviceName;
	private $class;
	private $methodName;
	private $method;
	
	public function __construct($serviceName, $methodName)
	{
		$this->serviceName = $serviceName;
		$this->class = TypeLoader::loadType($serviceName);
		$this->methodName = $methodName;
		$this->method = $this->class->getMethod($this->methodName);
	}
	
	public function getNamespace()
	{
		/*int*/ $lastDotIndex = strrpos($this->serviceName, "." );
		$namespace = substr($this->serviceName,0,$lastDotIndex);
		return $namespace;
	}
	
	public function getClassName()
	{
		return $this->class->getName();
	}
	
	public function getMethodName()
	{
		return $this->methodName;
	}
	
	public function getParameters()
	{
		$this->method->getParameters();
	}
	
	public function getReflactionClass()
	{
		return $this->class;
	}
	
	public function getReflactionMethod()
	{
		return $this->method;
	}
}
?>
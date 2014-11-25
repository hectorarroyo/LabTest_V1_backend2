<?php
class dbHandler {
	private $link;
		
	public  function __construct(){
		if(!defined("WebOrbServicesPath"))
		{
			$dom = new DomDocument();
	        $dom->load(WebOrb . "weborb-config.xml");
	
	        $servicePath = $dom->documentElement->getAttribute('servicePath');
	        define("WebOrbServicesPath", realpath(WebOrb . $servicePath ) . DIRECTORY_SEPARATOR);
		}
		if(file_exists(WebOrbServicesPath . "Weborb/Examples/Security/datbase.db"))
		{
			if(!$this->link = sqlite_open(WebOrbServicesPath . "Weborb/Examples/Security/datbase.db", 0666, $sqliteerror))
				throw new Exception($sqliteerror);	
		}
		else
		{
			if(!$this->link = sqlite_open(WebOrbServicesPath . "Weborb/Examples/Security/datbase.db", 0666, $sqliteerror))
				throw new Exception($sqliteerror);	
			sqlite_query($this->link,"CREATE TABLE Security ( Id INT, UserName VARCHAR(20), Password VARCHAR(20), Role VARCHAR(20) )");
			sqlite_query($this->link,"INSERT INTO Security (Id, UserName, Password, Role) VALUES ( 1, 'joe', 'flexrocks', 'examplesuser' )");
			sqlite_query($this->link,"INSERT INTO Security (Id, UserName, Password, Role) VALUES ( 2, 'bob', 'weborb', 'administrator' )");
			sqlite_query($this->link,"CREATE TABLE Products ( Id INT, Name VARCHAR(20), Price NUMERIC )");
			sqlite_query($this->link,"INSERT INTO Products (Id, Name, Price) VALUES ( 1, 'Laptop', 499 )");
			sqlite_query($this->link,"INSERT INTO Products (Id, Name, Price) VALUES ( 2, 'Mouse', 20 )");
			sqlite_query($this->link,"INSERT INTO Products (Id, Name, Price) VALUES ( 3, 'Keyboard', 10 )");
		}
	}
	
	public function getDbHandler()
	{
		return $this->link;
	}
	public function close()
	{
		sqlite_close($this->link);
	}
}

?>

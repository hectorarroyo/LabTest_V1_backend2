<?php

class CustomersDataService
{
    public function getCustomers()
    {

    	$link = mysql_connect("localhost", "flexuser", "password");

		if( !$link )
			throw new Exception( "cannot connect to mysql database" );

		if( !mysql_select_db('northwind', $link) )
			throw new Exception( "cannot select northwind database, make sure to run northwing.sql from /Services/Weborb/tests" );

		$qr = mysql_query("SELECT * FROM customers order by CustomerID")or die("Invalid query: " . mysql_error());
	    mysql_close($link);
        return $qr;
    }
}

?>
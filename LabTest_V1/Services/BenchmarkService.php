<?php

class BenchmarkService
{
	public function echoArray( $arr )
	{
		return $arr;
	}

	public function getArray( $size )
	{
		$arr = array();

		for( $i = 0; $i < $size; $i++ )
		{
			$arr[ $i ] = "string " . $i;
		}

		return $arr;
	}
	
    public function getCustomers( $size )
    {

    	$link = mysql_connect("localhost", "root", "sa");

		if( !$link )
			throw new Exception( "cannot connect to mysql database" );

		if( !mysql_select_db('weborbbenchmark', $link) )
			throw new Exception( "cannot select northwind database, make sure to run northwing.sql from /Services/Weborb/tests" );

		$result = mysql_query("SELECT * FROM census limit "  .$size ." ;")or die("Invalid query: " . mysql_error());

	    mysql_close($link);

        return $result;
    }

}
?>
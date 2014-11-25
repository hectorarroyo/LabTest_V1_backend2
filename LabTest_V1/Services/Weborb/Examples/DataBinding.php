<?php
/*******************************************************************
 * DataBinding.php
 * Copyright (C) 2006-2007 Midnight Coders, LLC
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is WebORB Presentation Server (R) for PHP.
 *
 * The Initial Developer of the Original Code is Midnight Coders, LLC.
 * All Rights Reserved.
 ********************************************************************/
class DataBinding
{
    public function getCustomers()
    {

    	$link = mysql_connect("localhost", "flexuser", "password");

		if( !$link )
			throw new Exception( "cannot connect to mysql database" );

		if( !mysql_select_db('northwind', $link) )
			throw new Exception( "cannot select northwind database, make sure to run northwing.sql from /Services/Weborb/tests" );

		$qr = mysql_query("SELECT * FROM customers order by CustomerID")or die("Invalid query: " . mysql_error());
		$result = array();
		while($rez = mysql_fetch_array($qr))
		{
			$customer["Address"] = $rez["Address"];
			$customer["City"] = $rez["City"];
			$customer["CompanyName"] = $rez["CompanyName"];
			$customer["ContactName"] = $rez["ContactName"];
			$customer["ContactTitle"] = $rez["ContactTitle"];
			$customer["Country"] = $rez["Country"];
			$customer["CustomerID"] = $rez["CustomerID"];
			$customer["PostalCode"] = $rez["PostalCode"];
			$result[] = $customer;
		}
	    mysql_close($link);

        return $result;
    }

    public function updateCustomer(Customer $originalCustomerRecord, $changedFieldName = null, $newValue = null)
    {
    	$link = mysql_connect("localhost", "flexuser", "password");

    	mysql_select_db('northwind', $link);

    	if ($changedFieldName == null && $newValue == null)
    	{
    		$query = "UPDATE Customers set  Address = 		'$originalCustomerRecord->Address',
											City = 			'$originalCustomerRecord->City',
											CompanyName = 	'$originalCustomerRecord->CompanyName',
											ContactName = 	'$originalCustomerRecord->ContactName',
											ContactTitle = 	'$originalCustomerRecord->ContactTitle',
											Country =	 	'$originalCustomerRecord->Country',
											PostalCode = 	'$originalCustomerRecord->PostalCode' ";
    	}
    	else
    	{
    		$query = "UPDATE Customers set $changedFieldName = '$newValue' ";
    	}

    	$query .= " WHERE CustomerID = '$originalCustomerRecord->CustomerID';";

    	$result = mysql_query($query) or die("Invalid query: " . mysql_error());

	    mysql_close($link);
    }
}
class Customer
{
	var $Address;
	var $City;
	var $CompanyName;
	var	$ContactName;
	var $ContactTitle;
	var $Country;
	var $PostalCode;
	var $CustomerID;
}
?>
<?php
/*******************************************************************
 * ExceptionsTest.php
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
	// This simple class demonstrates how application exceptions
    // get propagates to the client. The client application
    // invokes various methods available in the class. The method 
    // cause an exception to happen. In all cases exceptions delivered
    // to the calling program as faults.
require_once("MyException.php");

class ExceptionsTest
{
    public function divByZero()
    {
        throw new Exception("Divide by zero exception");
    }

    public function NPE()
    {
        throw new Exception("Null pointer exception");
    }

    public function throwException()
    {
        throw new MyException( "this is a custom application exception" );
    }
}
?>
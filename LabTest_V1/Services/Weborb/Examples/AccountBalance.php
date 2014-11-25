<?php
/*******************************************************************
 * AccountBalance.php
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
// Access to this class s secured in WEB-INF/flex/remoting-config.xml.
// Only requests with the right set of credentially will be given access
// to invoke method in this class
class AccountBalance
{
// The method is invoked by Flex client with the right set of user credentials
// The implementation of the method is not important for the example, only
// the fact that unsecure invocations are not allowed

	public function CheckBalance()
	{
	    return rand(0, 1000000);
	}
}
?>
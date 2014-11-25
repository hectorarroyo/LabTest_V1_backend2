<?php
/*******************************************************************
 * Invocation.php
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



require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/ThreadContext.php");
require_once(WebOrb . "Exceptions/WebORBAuthenticationException.php");


class Invocation
{

    static public function invoke($object, ReflectionMethod $method, $arguments)
    {
        $parameters = $method->getParameters();

        $args = array();

        for($i = 0; $i < count($arguments); $i++)
        {
            $arg = $arguments[$i];

            if (is_null($parameters[$i]->getClass()))
            {
                if ($arg instanceof IAdaptingType)
                    $args[] = $arg->defaultAdapt();
                else
                    $args[] = $arg;
            }
            else
            {
                if ($arg instanceof IAdaptingType)
                    $args[] = $arg->adapt($parameters[$i]->getClass());
                else
                    $args[] = $arg;
            }
        }



        try
        {
		    $class = new ReflectionClass( 'ReflectionMethod' );
 		    $invokeArgsMethod = $class->getMethod( 'invokeArgs' );
        }
        catch( Exception $e )
        {
			if( sizeof( $args ) == 1 )
			   $args = $args[ 0 ];

			$result = $method->invoke($object, $args);

			if( is_array( $args ) && sizeof($parameters) > 1 )
			   $result = $result[ 0 ];
			
			return $result;
        }     
       
		return $method->invokeArgs($object, $args);

    }

}

?>
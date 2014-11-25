<?php
/*******************************************************************
 * WebORBAuthorizationHandler.php
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

require_once(WebOrb . "Security/IAuthorizationHandler.php");
 
class WebORBAuthorizationHandler
    implements IAuthorizationHandler
{
  
		public /*bool*/function  AuthorizeAccess(/*String*/ $resource, ORBSecurity $security)
        {
            $resource = ServiceRegistry::GetMapping($resource);
            $accessConstraintList = $security->GetConstraints($resource);
			$grantConstraints = array();
            $rejectConstraints = array();
            /*int*/ $currentPriority = 0;

            while ($currentPriority < count($accessConstraintList))
            {
                /*StringCollection*/ $accessConstraintsNames = (array)$accessConstraintList[$currentPriority];//StringCollection accessConstraintsNames = accessConstraintList[currentPriority];
               
                foreach ($accessConstraintsNames as /*string*/$constraintName)
                {
                    /*AccessConstraint*/ $constraint = $security->getAccessConstraint($constraintName);//AccessConstraint constraint = (AccessConstraint)security.getAccessConstraint(constraintName);

                    if ($constraint->IsGrant())
                        array_push($grantConstraints,$constraint);
                    else
                        array_push($rejectConstraints,$constraint);
                }
                
                ++$currentPriority;
            }
			
                foreach ($grantConstraints as $constraint)
                {
                   
                    if ($constraint->Validate())
                    {
                        Log::log(LoggingConstants::SECURITY, "access allowed. resource name - '".$resource."'. reason - ".$constraint->GetReason());                        
                        return true;
                    }
                }

                foreach ($rejectConstraints as $constraint)
                {
                    if (!$constraint->Validate())
                    {
                        Log::log(LoggingConstants::SECURITY, "access denied. resource name - '".$resource."'. reason - ".$constraint->GetReason());
                        return false;
                    }
                }
            
            
            if ($security->GetDeploymentMode() == ORBSecurity::CLOSEDSYSTEM_MODE)
            {
                Log::log(LoggingConstants::SECURITY, "access to resource ".$resource." has been denied. WebORB Closed-System Mode requires explicit access declaration for all resources");
                return false;
            }

            return true;
        }	

}

?>
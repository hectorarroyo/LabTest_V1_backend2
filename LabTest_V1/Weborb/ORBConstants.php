<?php
/*******************************************************************
 * ORBConstants.php
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
class ORBConstants
{
	const PERSISTENTWEBSERVICESSTORE = "persistentWebServicesStore";
	/*String*/const MONITOREDSERVICE = "monitoredService";
  	/*String*/const MONITOREDSERVICES = "monitoredServices";
	/*String*/const RBISERVERCONFIGURATION = "serverConfiguration";
	/*String*/const BUSINESSINTELLIGENCE = "businessIntelligence";
    const ONRESULT = "/onResult";
    const ONSTATUS = "/onStatus";

    // service context
    const ACTIVATION = "activation";

    const LOG = "log";
	const CURRENT_POLICY = "currentPolicy";
	const LOGGING_POLICY = "loggingPolicy";
	const ENABLE = "enable";
	const YES = "yes";
	const NO = "no";
	const POLICY_NAME = "policyName";
	const PARAMETER = "parameter";
	const RESPONSE_METADATA = "responseMetadata";
	const MESSAGE_SERVICE_HANDLER = "message-service-handler";
	const MESSAGE_STORAGE_POLICY = "message-storage-policy";
	
	const SERVER = "server";
	const ALLOW_SUBTOPICS = "allow-subtopics";
	const SUBTOPIC_SEPARATOR = "subtopic-separator";	  
}
?>
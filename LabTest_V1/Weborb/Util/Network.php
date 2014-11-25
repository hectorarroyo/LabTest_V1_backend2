<?php
/*******************************************************************
 * Network.php
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

 class Network
 {
  private static $s_privateIPArray=array(
                       "10.0.0.0/8",
                       "172.16.0.0/12",
                       "192.168.0.0/16");

  public static function getUserIPArray()
  {
    $ipList = array();
    
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
    {
      if(strpos($_SERVER['HTTP_X_FORWARDED_FOR'],',') !== false)
       $ipList +=  explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
      else
       $ipList[] = $_SERVER['HTTP_X_FORWARDED_FOR'];    
    }
    
    $ipList[] = $_SERVER['REMOTE_ADDR'];
    
    return $ipList;      
  }

  public static function isIPInNet($ip,$net,$mask) 
  {
    $lnet=ip2long($net);
    $lip=ip2long($ip);
    $binnet=str_pad( decbin($lnet),32,"0","STR_PAD_LEFT" );
    $firstpart=substr($binnet,0,$mask);
    $binip=str_pad( decbin($lip),32,"0","STR_PAD_LEFT" );
    $firstip=substr($binip,0,$mask);
                                                                               
    return(strcmp($firstpart,$firstip)==0);
 }
 
 public static function isIpInNetArray($ip,&$array)
 {
  $returnValue = false;
  
  foreach ( $array as $subnet ) 
  {
   list($net,$mask)=split("/",$subnet);
   
   if(Network::isIPInNet($ip,$net,$mask))
   {
     $returnValue = true;
     break;
   }
  }
  return $returnValue;
 }
 
 public static function isIpInPrivateNetwork($ip)
 {
    return self::isIpInNetArray($ip,self::$s_privateIPArray);  
 }
 
 public static function getUserHostAddress()
 {
   $ip = "unknown";
   $ip_array = self::getUserIPArray();
   
   foreach ( $ip_array as $ip_s ) 
   {                                                           
     if( $ip_s !="")
        $ip = $ip_s;
     else
        continue;
    
     if(!Network::isIPInNetArray($ip_s,self::$s_privateIPArray))
       break;
   }
                                                                                                                                                               
    return $ip;
  } 
  
  public static function getUserHostName()
  {
    $remouteHost = $_SERVER['REMOTE_HOST'];
    
    if($remouteHost == null || strlen($remouteHost) == 0)
        $remouteHost = gethostbyaddr(self::getUserHostAddress());
        
    return $remouteHost;
  }

  public static function getServerIp()
  {

    return gethostbyname($_SERVER["SERVER_NAME"]);
  }
  
  public static function getServerName()
  {

    return $_SERVER["SERVER_NAME"];
  }
  
  public static function getLocalIp()
  {
    return gethostbyname("localhost");    
  }
  
}
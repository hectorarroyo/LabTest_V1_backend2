<?php
require_once(WebOrb . "Security/IRolesProvider.php");
class WebORBRolesProvider implements IRolesProvider
{
  public /*String[]*/function getRoles()
  {
    return ORBConfig::getInstance()->getSecurity()->getRoles();
  }

  public /*String[]*/function getUserRoles( /*String*/ $userName )
  {
    return ORBConfig::getInstance()->getSecurity()->getUserRoles( $userName );    
  }
}
?>
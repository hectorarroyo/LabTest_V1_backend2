<?php
require_once(WebOrb . "Security/IRolesProvider.php");
require_once("dbHandler.php");

class CustomRolesProvider implements IRolesProvider
{
  private $link;
  private $db;	
	
  public function __construct()
  {
  	$this->db = new dbHandler();
	$this->link = $this->db->getDbHandler();	
  }
  
    
  public /*String[]*/function getRoles()
  {
    return sqlite_array_query($this->link, "SELECT Role FROM Security", SQLITE_NUM);
  }

  public /*String[]*/function getUserRoles( /*String*/ $userName )
  {
  	$arr = sqlite_array_query($this->link, "SELECT Role FROM Security where UserName = '" . $userName . "'", SQLITE_NUM );
    return $arr[0];
  }
  
}
?>
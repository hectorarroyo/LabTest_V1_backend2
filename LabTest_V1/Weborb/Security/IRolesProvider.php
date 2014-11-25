<?php
interface IRolesProvider 
{
	 public /*String[]*/function getRoles();
     public /*String[]*/function getUserRoles( /*String*/ $userName );
}
?>
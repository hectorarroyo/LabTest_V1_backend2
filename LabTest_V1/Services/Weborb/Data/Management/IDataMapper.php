<?php
require_once("ActiveQuery.php");

interface IDataMapper
{
    public /*bool*/ function IsInQuery( /*DomainObject*/ $domainObject, /*ActiveQuery*/ $activeQuery);
    public /*String*/ function getSafeName( /*String*/ $name);
}

?>
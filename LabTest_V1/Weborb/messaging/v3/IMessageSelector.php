<?php
//require_once(WebOrb . "V3Types/V3Message.php");
interface IMessageSelector 
{
    function setClientSelectorValue( /*String*/ $selectorValue );
    /*Object*/function processClientMessage( /*V3Message*/ $message );
    /*Object*/function processServerMessage( /*Object*/ $message );
    function setClientId( /*String*/ $clientId );
    /*String*/function getClientId();
}
?>

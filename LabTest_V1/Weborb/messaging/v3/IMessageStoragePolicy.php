<?php
interface IMessageStoragePolicy 
{
	public function addMessage( /*Object*/ $message );	
	public /*ArrayList*/function getMessages( /*Subscriber*/ $subscriber );
	public function cleanUp();

}
?>

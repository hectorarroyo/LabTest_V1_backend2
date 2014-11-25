<?php
interface IServiceHandler 
{
  public function initialize( /*IDestination*/ $destination );
  public function handleSubscribe( /*Subscriber*/ $subscriber );
  public function handleUnsubscribe( /*Subscriber*/ $subscriber );
  public /*ArrayList*/function getMessages( /*Subscriber*/ $subscriber );
  public function addMessage( /*Hashtable*/ $properties, /*Object*/ $message );
}
?>
<?php
require_once(WebOrb . 'Util/Cache/Cache.php');

class SubscriptionsManager
{
  private static /*SubscriptionsManager*/ $instance;
  private /*Hashtable*/ $subscribers = array();

  public static /*SubscriptionsManager*/function getInstance()
  {
    if( self::$instance == null )
      self::$instance = new SubscriptionsManager();

    return self::$instance;
  }

  public function addSubscriber( /*String*/ $dsId, /*Subscriber*/ $subscriber )
  {
  	$this->subscribers = Cache::get("subscribers");
    $this->subscribers[$dsId] = $subscriber;
    Cache::put("subscribers",$this->subscribers);
  }

  public /*Subscriber*/function getSubscriber( /*String*/ $dsId )
  {
  	$this->subscribers = Cache::get("subscribers");
    return $this->subscribers[$dsId];
  }

  public function removeSubscriber( /*String*/ $dsId )
  {
  	$this->subscribers = Cache::get("subscribers");
    unset($this->subscribers[$dsId]);
    Cache::put("subscribers",$this->subscribers);
  }
}
?>

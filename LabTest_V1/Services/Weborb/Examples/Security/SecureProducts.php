<?
require_once("dbHandler.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");
class SecureProducts
{
  public function getProducts()
  {
    try
    {
    	$db = new dbHandler();
		$link = $db->getDbHandler();
		
        /*String*/ $query = "SELECT Name, Price FROM Products";
        
		$result = sqlite_array_query($link, $query, SQLITE_ASSOC);
        $db->close();
        return $result;
    }
    catch( Exception $exception )
    {
     	Log::log( LoggingConstants::EXCEPTION, $exception );
    }

    return null;
  }
}
?>
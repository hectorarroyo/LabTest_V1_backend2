<?php

abstract class Responder
{
  public abstract function responseHandler( /*Object*/ $adaptedObject );

  public abstract function errorHandler( /*Fault*/ $fault );
}
?>
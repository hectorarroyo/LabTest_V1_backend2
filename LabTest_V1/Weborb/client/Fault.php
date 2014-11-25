<?php
class Fault
{
  private /*String*/ $message;
  private /*String*/ $detail;

  public function __construct( /*String*/ $message, /*String*/ $detail )
  {
    $this->message = $message;
    $this->detail = $detail;
  }

  public /*String*/ function getMessage()
  {
    return $this->message;
  }

  public /*String*/ function getDetail()
  {
    return $this->detail;
  }
}
?>
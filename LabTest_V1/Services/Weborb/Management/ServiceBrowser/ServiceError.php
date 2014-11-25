<?php
class returnMes
{
	public $error_mes;
	public function __construct($error_mes)
	{
		$this->error_mes = $error_mes;
	}
}
class ServiceError {
	public function getError()
	{
		$handle = fopen(ini_get("error_log"), "r");

		if( $handle != null )
		{
			while (!feof($handle)) {
				$buffer = fgets($handle);
				if ($buffer != "" && strpos($buffer, "PHP Warning") === false && strpos($buffer, "PHP Notice") === false)
				{
					$last_error = $buffer;
				}
			}
			fclose($handle);
			return new returnMes($last_error);
		}

		return "could not locate error file";
	}

}
?>

<?php



class generalService //mismo nombre que el archivo
{
	public function generalService()
	{
		
	}
	
	
	/**prueba de servicios
	* **/
	
	public function getAll()
	{
		
			return "OK";
	}
	/**Prueba de servicios que recibe un par‡metro0**/
	public function ins($table, $campos, $datos)
	{
		$sql = "";
		$sql .= "INSERT INTO " . $table . " SET ";
		$noCampos = count($campos) - 1;
		for($i = 0 ; $i <= $noCampos; $i++)
		{
			if($i != $noCampos)
			{
				$sql .= "".$campos[$i]." = '".$datos[$i] . "', ";
			}else
			{
				$sql .= "".$campos[$i]." = '".$datos[$i] . "' ;";
			}
		}
		
		return $sql;
	}
	
}


?>
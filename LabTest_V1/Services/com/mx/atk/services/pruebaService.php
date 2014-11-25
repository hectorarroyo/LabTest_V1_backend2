<?php
//cambio desde linea hecha por cuenta externa
require_once ("generalService.php");

class pruebaService extends generalService //mismo nombre que el archivo
{
	public function pruebaService()
	{
	}
	
	
	/**prueba de servicios
	* **/
	
	public function getAll()
	{
		
			return "OK";
	}
	/**Prueba de servicios que recibe un par‡metro0**/
	public function sumaVar($SumVar)
	{
		$var =  5;
		$sum = 0;
		
		$sum = $SumVar + $sum;
		
		return $sum;
	}
	
	
	
	
}

//cambio numero dos realizado cuando se hizo un cambio en linea con cuenta externa
$test = 110;

$test = 234591;



//prueba uno
$table = "tabla_prueba";
$campos = array("Id", "Nombre", "Numero");
$datos  = array("1", "Nombre del campo", "35");

$prueba = new pruebaService();

$rst1 = $prueba->ins($table, $campos, $datos);

echo $rst1."<br />";

$table = "tabla_prueba_dos";
$campos = array("Id", "Nombre", "Numero", "Informacion");
$datos  = array("2", "Nombre del campo 2", "3445", "Test de Informacion campo adicional");

$prueba = new generalService();

$rst1 = $prueba->ins($table, $campos, $datos);

echo $rst1."<br />";



?>

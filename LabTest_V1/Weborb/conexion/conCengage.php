<?php

if (!class_exists("MySQL")){
	class MySQL{
		private $conexion;
		private $total_consultas;
		private $ult_insert_Id;

		public function MySQL(){
			if(!isset($this->conexion)){


				$this->conexion = (mysql_connect("localhost","root","root")) or die(mysql_error());
				mysql_select_db("testgenerator",$this->conexion) or die(mysql_error());
				 // mysql_select_db("logic_estrella",$this->conexion) or die(mysql_error());
				mysql_query("SET NAMES 'utf8'");
				mysql_query("SET character_set_results = 'latin1'");

			}
		}

		public function consulta($consulta){
			$this->total_consultas++;
			$resultado = mysql_query($consulta,$this->conexion);
//			mysql_query("SET NAMES 'utf8'");
//			mysql_query("SET character_set_results = 'latin1'");
			if(!$resultado){
				echo 'MySQL Error: ' . mysql_error();
				 exit;
			}
			return $resultado;
		}

		public function fetch_array($consulta){
			return mysql_fetch_array($consulta);
		}

		public function num_rows($consulta){
			return mysql_num_rows($consulta);
		}

		public function getTotalConsultas(){
			return $this->total_consultas;
		}
	}
}
?>

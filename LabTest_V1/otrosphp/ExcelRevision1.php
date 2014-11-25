<?php

header('ETag: etagforie7download'); //IE7 requires this header
header('Content-type: application/octet_stream');
header('Content-disposition: attachment; filename="Excel1.xls"');

require_once ("../Weborb/conexion/conScript.php");

/**
 * Declaracion de variables.
 */
//$Mes = 0;
$Mes = $_GET['Mes'];
//$Anio = 0;
$Anio = $_GET['Anio'];


$excel = "";
$header1Antes = "";
$header2Antes = "";
$header1Durante = "";
$header2Durante = "";
$header1Despues = "";
$header2fDespues = "";
$tiendacol = "";

$arrayCampos = array();
$maxColEmp = 0; //Cuantas columnas maximas de empleados.
$contColAntes = 0; // Cuantas columnas del Antes se han escrito
$contColDurante = 0; //Cuantas columnas del Durante se han escrito.
$contColDespues = 0; //Cuantas columnas del Durante se han escrito.
$contCol = 0;



$excel .= "<table border = '1'>";
/**
 * Header();
 */
/*Antes*/
$header2Antes = "";
$header2Antes .= EscribirHeaderAntesCol("Num");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Sucursal");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("FODA");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Dirección");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Nombre de Gerente");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Evaluación");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Fecha de Ingreso Gerente");
$contColAntes ++;

$rst0 = mysql_query("select MAX(EmpleadosIdeal) as emp_maximo from tiendas;");
$maxColEmp = mysql_result($rst0,0,'emp_maximo');


for($i=1;$i<=$maxColEmp;$i++){
	$header2Antes .= EscribirHeaderAntesCol("Nombre ".$i);
	$contColAntes ++;
	$header2Antes .= EscribirHeaderAntesCol("Ingreso ".$i);
	$contColAntes ++;
	$header2Antes .= EscribirHeaderAntesCol("Semillero ".$i);
	$contColAntes ++;
	$header2Antes .= EscribirHeaderAntesCol("Gerenciamiento ".$i);
	$contColAntes ++;
}

$header2Antes .= EscribirHeaderAntesCol("Crecimiento vs Año Anterior");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Avance Real de Presupuesto");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Promedio Anual de Ventas");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Cheque Promedio Mes");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Meses Inventario");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Mystery Shopper");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Evaluacion Inventario al día de la visita");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Acumulado de Venta al Día de Visita");
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Avance de Presupuesto del Mes");
$contColAntes ++;

$contCol += $contColAntes;

/*Durante*/
$result = mysql_query("select * from cat_eval_campos where Activo = 1 order by cat_eval_Id, Nombre;");

while($row = mysql_fetch_object($result)){
	$header2Durante .= EscribirHeaderDuranteCol($row->Nombre);
	$contColDurante ++;
	$arrayCampos[] = $row->Id;
}
$contCol += $contColDurante;

/*Despues*/
$header2Despues = EscribirHeaderDespuesCol("TO-DO");;
$contColDespues ++;
$contCol += $contColDespues;

$excel .= "<tr>";
$excel .= "<th colspan = '".$contColAntes."'> Antes </th>";
$excel .= "<th colspan = '".$contColDurante."'> Durante </th>";
$excel .= "<th colspan = '".$contColDespues."'> Después </th>";
$excel .= "</tr>\n";
$excel .= "<tr>";
$excel .= $header2Antes;
$excel .= $header2Durante;
$excel .= $header2Despues;
$excel .= "</tr>\n";

/**
 * Informacion()
 */
 $contCol = 0;

$consulta0 = "select rev.*, ".
		"ti.NumSuc as tiendas_NumSuc, ".
		"ti.Nombre as tiendas_Nombre, ".
		"ti.FODA as tiendas_FODA,  ".
		"ti.Direccion as tiendas_Direccion ".
		"from revisiones as rev ".
		"left join tiendas as ti on rev.tiendas_Id = ti.Id " .
		"WHERE MesRev = ".$Mes." and AnioRev = ".$Anio . " and Status = 131";
//echo $consulta0;
$result = mysql_query($consulta0);

while($row = mysql_fetch_object($result)){
	$tiendacol .= "<tr>";
	$tiendacol .= EscribirCol($row->tiendas_NumSuc);
	$tiendacol .= EscribirCol($row->tiendas_Nombre);
	$tiendacol .= EscribirCol($row->tiendas_FODA);
	$tiendacol .= EscribirCol($row->tiendas_Direccion);

	/*Informacion del gerente*/
	$rst0 = mysql_query("select temp.*, ".
			"CONCAT(emp.ApePat,' ',emp.ApeMat,' ',emp.Nombre) as empleados_NombreCompleto ".
			"from tiendas_empleados as temp  ".
			"left join empleados as emp on temp.empleados_Id = emp.Id ".
			"where tiendas_Id = ".$row->tiendas_Id." and TipoPuesto = 71");
	if(mysql_num_rows($rst0)!=0){
		if($row0 = mysql_fetch_object($rst0)){
			$tiendacol .= EscribirCol($row0->empleados_NombreCompleto);
			$tiendacol .= EscribirCol($row0->Evaluacion);
			$tiendacol .= EscribirCol(formatoFecha($row0->FhAsignacion));
		}
	}else{
		$tiendacol .= EscribirCol("No Asignado");
		$tiendacol .= EscribirCol("");
		$tiendacol .= EscribirCol("");
	}
	$rst2 = mysql_query("select temp.*, ".
			"CONCAT(emp.ApePat,' ',emp.ApeMat,' ',emp.Nombre) as empleados_NombreCompleto ".
			"from tiendas_empleados as temp  ".
			"left join empleados as emp on temp.empleados_Id = emp.Id ".
			"where tiendas_Id = ".$row->tiendas_Id." and TipoPuesto <> 71;");
	$i = 0;
	if(mysql_num_rows($rst2)!=0){
		if($i<$maxColEmp){
			while($row2 = mysql_fetch_object($rst2)){
				$tiendacol .= EscribirCol($row2->empleados_NombreCompleto);
				$tiendacol .= EscribirCol(formatoFecha($row2->FhAsignacion));
				$tiendacol .= EscribirCol(($row2->Semillero==1)?"Si":"No");
				$tiendacol .= EscribirCol(($row2->Gerenciamiento==1)?"Si":"No");
				$i++;
			}
		}
	}
	while($i<$maxColEmp){
		$tiendacol .= EscribirCol("");
		$tiendacol .= EscribirCol("");
		$tiendacol .= EscribirCol("");
		$tiendacol .= EscribirCol("");
		$i++;
	}

	/*Obtenemo Datos de la Consulta Antes*/
	$consulta = "select tim.*, ".
				"ti.NumSuc as tiendas_NumSuc, ".
				"ti.Nombre as tiendas_Nombre, ".
				"cl.Nombre as tienda_mes_clasificacion_Id_Desc, ".
				"ti.FODA as tiendas_FODA,  ".
				"st.Nombre as TipoMercardo_Desc, ".
				"st1.Nombre as tienda_mes_StatusPrep_Desc, ".
                "cl.Nombre as tiendas_clasificacion_Id_Desc ".
				"from tienda_mes as tim  ".
				"left join tiendas as ti on tim.tiendas_Id = ti.Id ".
				"left join com_status as st on ti.TipoMercado = st.Id  ".
				"left join com_status as st1 on tim.StatPresup = st1.Id ".
                "left join clasificaciones as cl on tim.clasificacion_Id = cl.Id ".
				"where tim.tiendas_Id = ".$row->tiendas_Id." and tim.Anio = ".$Anio." and tim.Mes = ".$Mes.";";

	$rst3 = mysql_query($consulta);

	if($row3 = mysql_fetch_object($rst3)){

		if($Mes == 1){
			$MesAnt = 12;
			$datVO->Anio = $Anio - 1;
		}else{
			$MesAnt = $Mes - 1;
		}

		$tienda_mes_ETAR =$row3->ETAR;
		$tienda_mes_ChqPromMes = $row3->ChqPromMes;
		$tienda_mes_PromAnualVtas =$row3->PromAnualVtas;
		$tienda_mes_CrecimAnioAnt =$row3->CrecimAnioAnt;
		$tienda_mes_AvanRealPres =$row3->AvanRealPres;
		$tienda_mes_MesesInv	=$row3->MesesInv;
		$tienda_mes_Inventario	=$row3->Inventario;
		$tienda_mes_Ventas	=$row3->Ventas;
		$tienda_mes_MetaDiario	=$row3->MetaDiario;
		$tienda_mes_Presupuesto	=$row3->Presupuesto;
		$Mystery0 = $row3->Mystery;

		//Cheque Promedio diario
		$rst6 = mysql_query("select * from tienda_dia WHERE Month(FhInfo) = ". $Mes ." and Year(FhInfo)=". $Anio ." order by FhInfo desc limit 1");
		$tienda_dia_FhInfo = mysql_result($rst6,0,'FhInfo');
		$tienda_dia_ChqPromDiario = mysql_result($rst6,0,'ChqPromDiario');
		$tienda_dia_DifVRealVDiaria = mysql_result($rst6,0,'DifVRealVDiaria');


	} else {
		$tienda_mes_ETAR = "N/A";
		$tienda_mes_ChqPromMes = "N/A";
		$tienda_mes_PromAnualVtas ="N/A";
		$tienda_mes_CrecimAnioAnt ="N/A";
		$tienda_mes_AvanRealPres ="N/A";
		$tienda_mes_MesesInv	="N/A";
		$tienda_mes_Inventario	="N/A";
		$tienda_mes_Ventas	="N/A";
		$tienda_mes_MetaDiario	="N/A";
		$tienda_mes_Presupuesto	="N/A";
		$Mystery0 = "N/A";
		$tienda_dia_ChqPromDiario = "N/A";
		$tienda_dia_DifVRealVDiaria = "N/A";
	}

	/*Escribimos Datos de la consulta_antes*/
	$tiendacol .= EscribirCol($tienda_mes_CrecimAnioAnt);
	$tiendacol .= EscribirCol($tienda_mes_AvanRealPres);
	$tiendacol .= EscribirCol($tienda_mes_PromAnualVtas);
	$tiendacol .= EscribirCol($tienda_mes_ChqPromMes);
	$tiendacol .= EscribirCol($tienda_mes_MesesInv);
	$tiendacol .= EscribirCol($Mystery0	);
	$tiendacol .= EscribirCol($tienda_mes_Inventario);
	$tiendacol .= EscribirCol($tienda_mes_Ventas);
	$tiendacol .= EscribirCol($tienda_mes_Presupuesto);

	/*Durante (Valores de los campos seleccionados)*/
	$arrayValores = array();
	$arrayTodos = array();

	$rst9 = $sqlconnect->consulta("select * from cat_eval_campos where Activo = 1 order by cat_eval_Id, Nombre;");
	$i = 0;
	while($row9 = mysql_fetch_object($rst9)){
		$rst10 = $sqlconnect->consulta("select rd.*, cev.Nombre as cevNombre from revision_datos as rd left join cat_eval_camposval as cev on rd.cat_eval_camposval = cev.Id " .
				"where revisiones_Id = " . $row->Id . " and cat_eval_campos = " . $row9->Id);
		if($row10 = mysql_fetch_object($rst10)){
			$arrayValores[$i++] = $row10->cevNombre;
		} else {
			$arrayValores[$i++] = "N/A";
		}
	}
	for($i = 0;$i<count($arrayValores);$i++){
		$tiendacol .= EscribirCol(($arrayValores[$i]==null)?"":$arrayValores[$i]);
	}

	$datTODO = "";
	$rst9 = $sqlconnect->consulta("select rt.Id, cec.Nombre as cecNombre, cev.Nombre as cevNombre " .
		"from revision_todo as rt " .
		"left join revision_datos as rd on rt.revision_datos_Id = rd.Id  ".
		"left join cat_eval_campos as cec on rd.cat_eval_campos = cec.Id  ".
		"left join cat_eval_camposval as cev on rd.cat_eval_camposval = cev.Id ".
		"WHERE rt.revisiones_Id = ".$row->Id);
	while($row9 = mysql_fetch_object($rst9)){
		$datTODO .= $row9->cecNombre . "[". $row9->cevNombre ."] ***";
	}
	$tiendacol .= EscribirCol($datTODO);

	$tiendacol .="</tr>\n";
	$excel .= $tiendacol;
	$tiendacol = "";
}

echo $excel;



function EscribirHeaderCol($texto){
	$columna = "";
	$columna .= "<th width='30'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirHeaderAntesCol($texto){
	$columna = "";
	$columna .= "<th width='100' style='background-color: #6B8E23'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirHeaderDuranteCol($texto){
	$columna = "";
	$columna .= "<th width='100' style='background-color: #FF8C00'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirHeaderDespuesCol($texto){
	$columna = "";
	$columna .= "<th width='500' style='background-color: #6B8E23'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirCol($texto){
	$columna = "";
	$columna .= "<td >";
	$columna .= ($texto=="")?"&nbsp;":$texto;
	$columna .= "</td>";
	return $columna;
}
function formatoFecha($sFecha){
	$sValores = explode("-", $sFecha);
	$sValores2 = explode(" ", $sValores[2]);
	return $sValores2[0]."-".$sValores[1]."-".$sValores[0].((strlen($sFecha)==10)?"":" ".$sValores2[1]);
}


?>

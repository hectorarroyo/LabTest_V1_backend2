<?php

header('ETag: etagforie7download'); //IE7 requires this header
header('Content-type: application/octet_stream');
header('Content-disposition: attachment; filename="Excel2.xls"');



require_once ("../Weborb/conexion/conScript.php");

/**
 * Declaracion de variables.
 */

//$Mes = 05;
$Mes = $_GET['Mes'];
//$Anio = 2010;
$Anio = $_GET['Anio'];

$MesesA = array( 1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre" );

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
$contCol = 0;



$excel .= "<table border = '1'>";
$excel .= "<tr height='40' style='font-size:18pt'>".
			"	<td colspan='5'>".
			"		<b>La Estrella de Cuernavaca SA de CV.</b>".
			"	</td>".
			"	</tr>";
$excel .= "<tr height='30'  style='font-size:12pt'>".
			"	<td colspan='5'>".
			"		<b>Tablero de Control General.</b>".
			"	</td>".
			"	</tr>";
$excel .= "<tr height='25' style='font-size:12pt'>".
			"	<td>".
			"		<b>Mes</b>".
			"	</td>".
			"	<td>".
			"		".$MesesA[$Mes].".".
			"	</td>".
			"	</tr>";
$excel .= "<tr height='25' style='font-size:12pt'>".
			"	<td>".
			"		<b>Anio</b>".
			"	</td>".
			"	<td>".
			"		".$Anio.".".
			"	</td>".
			"	</tr>";

//Header 1
$excel .= "<tr >";
for($i = 1;$i<=6;$i++){
	$excel .=
			"	<td>".
			"		".
			"	</td>";

}
$excel .= "".
			"	<td colspan='10' align='center' style='font-size:12pt'>".
			"		<b>FINANZAS</b>".
			"	</td>".
			"	<td colspan='12' align='center' style='font-size:12pt'>".
			"		<b>CLASIFICACIONES</b>".
			"	</td>".
			"	</tr>";
$excel .= "</tr>";

//Header 2
$excel .= "<tr >";
for($i = 1;$i<=6;$i++){
	$excel .=
			"	<td>".
			"		".
			"	</td>";

}
for($i = 1;$i<=2;$i++){
$excel .=
		"	<td border='1'>".
		"		".
		"	</td>";
}

$excel .=
	"	<td border='1' colspan='2' style='font-size:12pt'>".
	"		<b>Crecimiento " . ($Anio-1) ." & " . $Anio . "</b>.".
	"	</td>";
$excel .=
	"	<td border='1' colspan='2' style='font-size:12pt'>".
	"		<b>Cumplimiento c/presup.</b>.".
	"	</td>";

for($i = 1;$i<=2;$i++){
$excel .=
		"	<td border='1'>".
		"		".
		"	</td>";
}
$excel .= "</tr >";
/**
 * Header();
 */
/*Antes*/
$excel .= "<tr >";
$header2Antes = "";
$header2Antes .= EscribirHeaderAntesCol("No.", 60);
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Sucursal", 200);
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Clasif Mes", 50);
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Clasificación", 50 );
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Nombre Gerente", 250);
$contColAntes ++;
$header2Antes .= EscribirHeaderAntesCol("Fecha de Ingreso", 90);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Acumulado Inventarios", 110);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Meses Inventario", 80);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Acumulado", 80);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Mes", 80);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Mes", 50);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Anual", 50);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Cheque Promedio Mes", 110);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Cheque Promedio Anual", 110);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("PROCARD del Mes", 90);
$contColAntes ++;
$header2Antes .= EscribirHeaderDuranteCol("Acumulado ProCard", 90);
$contColAntes ++;

for($i=1;$i<=12;$i++){
	$header2Antes .= EscribirHeaderDespuesCol($MesesA[$i], 50);
	$contColAntes ++;
}
$excel .= $header2Antes;
$excel .= "</tr >";

/**
 * Informacion()
 */
$tiendacol = "";
$consulta = "select tm.*, ".
			"t.NumSuc as tiendas_NumSuc, ".
			"t.Nombre as tiendas_Nombre, ".
			"cl.Nombre as clasificaciones_Nombre ".
			"from tienda_mes as tm  ".
			"left join tiendas as t on tm.tiendas_Id = t.Id ".
			"left join clasificaciones as cl on tm.clasificacion_Id = cl.Id ".
			"where Anio = ".$Anio." and Mes = ".$Mes." ;";
$result = mysql_query($consulta);

while($row = mysql_fetch_object($result)){
	$tiendacol .= "<tr height='30'>";
	$tiendacol .= EscribirCol($row->tiendas_NumSuc, "center");
	$tiendacol .= EscribirCol($row->tiendas_Nombre);
	$tiendacol .= EscribirCol($row->clasificaciones_Nombre, "center");

	//Obtiene la clasificación que tiene dicha tienda para ese mes-año
	$FechaIniMes = $Anio . "-" . $Mes . "-01";
	$rst0 = mysql_query("select tc.*, ".
			"cl.Nombre as tienda_clasif_Nombre_Desc ".
			"from tienda_clasif as tc ".
			"left join clasificaciones as cl on tc.clasificaciones_Id = cl.Id ".
			"where tc.tiendas_Id = ".$row->tiendas_Id." and tc.FhClasif <= '".$FechaIniMes."' order by tc.FhClasif desc limit 1;");
	$tiendacol .= EscribirCol((mysql_num_rows($rst0)==0)?'':mysql_result($rst0, 0, 'tienda_clasif_Nombre_Desc'));

	//Obtiene el nombre del gerente
	$rst1 = mysql_query("select tm.*, ".
				"CONCAT(em.ApePat,' ',em.ApeMat,' ',em.Nombre) as  empleados_NombreCompleto ".
				"from tiendas_empleados as tm  ".
				"left join empleados as em on tm.empleados_Id = em.Id ".
				"where tiendas_Id = ".$row->tiendas_Id." and TipoPuesto = 71;");
	$tiendacol .= EscribirCol((mysql_num_rows($rst1)==0)?'':mysql_result($rst1,0,'empleados_NombreCompleto'));
	$fha = (mysql_num_rows($rst1)==0)?'':mysql_result($rst1,0,'FhAsignacion');
	$tiendacol .= EscribirCol($fha);

	$tiendacol .= EscribirCol(number_format($row->Inventario, 2, ".", ","), "right");
	$tiendacol .= EscribirCol($row->MesesInv, "center");

	$AnioAnt = $Anio - 1;
	//Crecimiento 2009 - Acumulado
	$rst3 = mysql_query("select SUM(Ventas) as SumVentas " .
			"from tienda_mes where tiendas_Id = ".$row->tiendas_Id." and Anio = ".$AnioAnt." and Mes <= ".$Mes." AND Status = 112");
	$promVentAnt = (mysql_num_rows($rst3)==0)?0:mysql_result($rst3,0,'SumVentas');
	$rst3 = mysql_query("select SUM(Ventas) as SumVentas " .
			"from tienda_mes where tiendas_Id = ".$row->tiendas_Id." and Anio = ".$Anio." and Mes <= ".$Mes." AND Status = 112");
	$promVentAct = (mysql_num_rows($rst3)==0)?0:mysql_result($rst3,0,'SumVentas');
	$diferVenta = $promVentAct - $promVentAnt;
	$porcIncrem = ($promVentAnt == 0)?100:($diferVenta / $promVentAnt)*100;
	$tiendacol .= EscribirCol(number_format($porcIncrem,2,'.',',')." %", "center");

	//Crecimiento 2009 - Mes
	$rst2 = mysql_query("select Ventas " .
			"from tienda_mes " .
			"where tiendas_Id = ".$row->tiendas_Id." and Anio = ".$AnioAnt." and Mes = ".$Mes.";");
	$ventaAnt = (mysql_num_rows($rst2) == 0)?0:mysql_result($rst2,0,'Ventas');
	$diferVenta = $row->Ventas - $ventaAnt;
	$porcIncrem = ($ventaAnt == 0)?100:($diferVenta / $ventaAnt)*100;
	$tiendacol .= EscribirCol(number_format($porcIncrem,2,'.',',')." %", "center");

	//Cumplimiento c/Presupuesto - Mes
	if ($row->Presupuesto == 0){
		$col = "NA";
	} else {
		$diferVenta = $row->Ventas - $row->Presupuesto;
		$col = ($diferVenta / $row->Presupuesto)*100;
	}
	$tiendacol .= EscribirPorcCol(number_format($col,2,'.',',')." %", "center");

	//Cumplimiento c/Presupuesto - Acumulado
	$rst4 = mysql_query("select SUM(Presupuesto) as SumaPres, SUM(Ventas) as SumaVentas " .
		"from tienda_mes where tiendas_Id = " . $row->tiendas_Id . " and Anio = " . $Anio . " and Mes <= ".$Mes." AND Status = 112 GROUP BY Anio");
	if (mysql_result($rst4,0,'SumaPres') == 0){
		$col = "NA";
	} else {
		$diferVenta = mysql_result($rst4,0,'SumaVentas') - mysql_result($rst4,0,'SumaPres');
		$col = ($diferVenta / mysql_result($rst4,0,'SumaPres'))*100;
	}
	$tiendacol .= EscribirPorcCol(number_format($col,2,'.',',')." %", "center");

	//ChqPromedio mes
	$tiendacol .= EscribirCol(number_format($row->ChqPromMes,2,'.',','), "right");

	//ChqPromedio Acumulado
	$rst6 = mysql_query("select SUM(ChqPromMes) as SumaChqPromMes, COUNT(Id) as totMeses from tienda_mes where tiendas_Id = ".$row->tiendas_Id." and Anio = ".$Anio." and Mes <= ".$Mes." AND Status = 112 GROUP BY Anio");
	$numMeses =mysql_result($rst6, 0, 'totMeses');
	$SumaChqProm = mysql_result($rst6, 0, 'SumaChqPromMes');
	$col = ($numMeses==0)?0:$SumaChqProm / $numMeses;
	$tiendacol .= EscribirCol(number_format($col,2,'.',','), "right", "right");

	//Tickets Mes
	$tiendacol .= EscribirCol(number_format($row->Tickets,2,'.',','), "right");

	//Suma tickets Anual
	$rst6 = mysql_query("select SUM(Tickets) as SumaTickets from tienda_mes where tiendas_Id = ".$row->tiendas_Id." and Anio = ".$Anio." and Mes <= ".$Mes." and Status = 112");
	$col = (mysql_num_rows($rst6)==0)?0:mysql_result($rst6,0,'SumaTickets');
	$tiendacol .= EscribirCol(number_format($col,2,'.',','), "right");

	//Clasificaciones
	for($i = 1;$i<=12;$i++){
		$rst7 = mysql_query("select tm.clasificacion_Id, cl.Nombre as clasificaciones_Nombre, tm.Mes  " .
					"from tienda_mes as tm ".
					"left join clasificaciones as cl on tm.clasificacion_Id = cl.Id ".
					"where tiendas_Id = ".$row->tiendas_Id." and Anio = ".$Anio." and Mes = ".$i.";");
		$col = (mysql_num_rows($rst7)==0)?'':mysql_result($rst7,0,'clasificaciones_Nombre');
		$tiendacol .= EscribirCol($col, "center");
	}
	$tiendacol .= "</tr>";
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
function EscribirHeaderAntesCol($texto, $ancho=100){
	$columna = "";
	$columna .= "<th width='" . $ancho . "' style='background-color: #6B8E23'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirHeaderDuranteCol($texto, $ancho=100){
	$columna = "";
	$columna .= "<th width='" . $ancho . "' style='background-color: #FF8C00'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirHeaderDespuesCol($texto, $ancho=100){
	$columna = "";
	$columna .= "<th width='" . $ancho ."' style='background-color: #6B8E23'>";
	$columna .= $texto;
	$columna .= "</th>";
	return $columna;
}
function EscribirCol($texto, $align="left"){
	$columna = "";
	$columna .= "<td align='$align'>";
	$columna .= $texto;
	$columna .= "</td>";
	return $columna;
}
function EscribirPorcCol($texto){
	$texto = number_format($texto,2);
	if($texto<80){
		$columna = "";
		$columna .= "<td style='background-color: #8B0000'>";
		$columna .= $texto."%";
		$columna .= "</td>";
	}else if($texto<95){
			$columna = "";
			$columna .= "<td style='background-color: #FFFF00'>";
			$columna .= $texto."%";
			$columna .= "</td>";
		}else if($texto>95){
			$columna = "";
			$columna .= "<td style='background-color: #2E8B57'>";
			$columna .= $texto."%";
			$columna .= "</td>";
		}

	return $columna;
}
function EscribirClasifCol($texto){

	return $columna;
}


?>

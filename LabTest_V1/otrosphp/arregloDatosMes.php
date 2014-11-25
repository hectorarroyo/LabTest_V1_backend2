<?php
/*
 * Este script sirve, para organizar los datos de la tabla tienda_mes y tienda día, esto por motivos de inserción de información inicial
 */

  //Conexion a la Base de datos
  set_time_limit(0);
  $link = mysql_connect("localhost","root","");
  mysql_select_db("comex");

  /*
   * Lee los datos de ventas diarias de forma acumulada, y los actualiza en mes.
   */
  	echo "Calculo 1";
  	echo "\nInicia:" . date('H:i:s');
  	$resultado = mysql_query("select tiendas_Id, sum(Ventas) as TotVentas, sum(Tickets) as TotTickets, YEAR(FhInfo) as Anio, MONTH(FhInfo) as Mes from tienda_dia GROUP BY tiendas_Id, YEAR(FhInfo), MONTH(FhInfo) ORDER BY tiendas_Id ", $link);
  	if(!$resultado){
    	echo "MySQL Error: " . mysql_error();
    	exit;
  	}
    while($row = mysql_fetch_object($resultado)){
      $TotVentas = $row->TotVentas;
      $TotTickets = $row->TotTickets;
    mysql_query("UPDATE tienda_mes SET " .
      "Ventas = " . $TotVentas . ", " .
      "Tickets = " . $TotTickets . " " .
      "WHERE tiendas_Id = " . $row->tiendas_Id . " AND Mes = " . $row->Mes . " AND Anio = " . $row->Anio, $link);
    }
    echo "\nFin:" . date('H:i:s');

  /*
   * Lee los datos de los meses existentes y coloca los que no requieren de información de ventas diaria
   */
   	echo "Calculo 2";
  	echo "\nInicia:" . date('H:i:s');
  	$resultado = mysql_query("SELECT * FROM tienda_mes ORDER BY Anio, Mes", $link);
 	if(!$resultado){
    	echo "MySQL Error: " . mysql_error();
    	exit;
  	}
    while($row = mysql_fetch_object($resultado)){
      	$MetDia = $row->Presupuesto / $row->PresDias;
      	$Clasif = 1;
	  	if ($row->Tickets == 0){
			$ChqMes = 0;
      	} else {
	      	$ChqMes = $row->Ventas / $row->Tickets;
      	}
      	$VtaProm = 0;
      	$CrecimAnio = 100;
      	$AvanceReal = 0;

    	//Busca la clasificación que le corresponde
    	$resCons2 = mysql_query("SELECT * FROM clasificaciones WHERE Desde < " . $row->Ventas . " AND Hasta >= " . $row->Ventas, $link);
      	if($rowCons2 = mysql_fetch_object($resCons2)){
			$Clasif = $rowCons2->Id;
      	}

	    //Se obtiene para ventas promedio
	    $consulta = "select Anio, Mes, count(Id) as TotMeses, sum(Ventas) as TotVentas " .
      		" from tienda_mes Where tiendas_Id = " . $row->tiendas_Id . " and Status = 112 and Anio = " . $row->Anio . " and Mes <= " . $row->Mes .
      		" group by Anio";
	    $resCons2 = mysql_query($consulta, $link);
      	if($rowCons2 = mysql_fetch_object($resCons2)){
        	$VtaProm = $rowCons2->TotVentas / $rowCons2->TotMeses;
      	}

	    //Se obtiene para crecimiento contra el año anterior
	    $resCons2 = mysql_query("select Ventas " .
      		" from tienda_mes Where tiendas_Id = " . $row->tiendas_Id . " and Anio = " . ($row->Anio-1) . " and Mes <= " . $row->Mes .
      		" group by Anio", $link);
      	if($rowCons2 = mysql_fetch_object($resCons2)){
        	$CrecimAnio = (($row->Ventas / $rowCons2->Ventas)-1)*100 ;
      	}

	    //Se obtiene para calcular el avance real en el presup
    	$resCons2 = mysql_query("select Anio, Mes, count(Id) as TotMeses, sum(Ventas) as TotVentas, sum(Presupuesto) as TotPresup " .
      		" from tienda_mes Where tiendas_Id = " . $row->tiendas_Id . " and Status = 112 and Anio = " . $row->Anio . " and Mes <= " . $row->Mes .
      		" group by Anio", $link);
      	if($rowCons2 = mysql_fetch_object($resCons2)){
	        $AvanceReal = (($rowCons2->TotVentas / $rowCons2->TotPresup)-1)*100;
      	}

    	mysql_query("UPDATE tienda_mes SET " .
      		" MetaDiario = " . $MetDia . ", " .
      		" clasificacion_Id = " . $Clasif . ", " .
      		" ChqPromMes = " . $ChqMes . ', ' .
      		" PromAnualVtas = " . $VtaProm . ', ' .
      		" CrecimAnioAnt = " . $CrecimAnio . ', ' .
      		" AvanRealPres = " . $AvanceReal . ' ' .
      		" WHERE Id = " . $row->Id, $link);
    }
    echo "\nFin:" . date('H:i:s');

  /*
   * Lee los datos del día, y los arregla.
   */
   	echo "Calculo 3";
  	echo "\nInicia:" . date('H:i:s');
 	$resultado = mysql_query("SELECT * FROM tienda_dia ORDER BY FhInfo", $link);
  	if(!$resultado){
    	echo "MySQL Error: " . mysql_error();
    	exit;
  	}
    while($row = mysql_fetch_object($resultado)){
    	$ChqPromDia = ((int)$row->Tickets == 0)?0:$row->Ventas/$row->Tickets;
    	$Mes = substr($row->FhInfo,5,2) * 1;
    	$Anio = substr($row->FhInfo,0,4) * 1;
    	$resCons2 = mysql_query("SELECT * FROM tienda_mes WHERE Mes = " . $Mes . " AND Anio = " . $Anio . " AND tiendas_Id = " . $row->tiendas_Id, $link);
    	$DifVentas = 0;
      	if($rowCons2 = mysql_fetch_object($resCons2)){
        	if ($rowCons2->MetaDiario != 0){
          		$DifVentas = $row->Ventas / $rowCons2->MetaDiario;
        	}
      	}
    	mysql_query("UPDATE tienda_dia SET " .
      		" ChqPromDiario = " . $ChqPromDia . ", " .
      		" DifVRealVDiaria = " . $DifVentas . " " .
      		" WHERE Id = " . $row->Id, $link);
    }
    echo "\nFin:" . date('H:i:s');

?>

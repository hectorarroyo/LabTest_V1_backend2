<?php
/*
 * Este script sirve, para organizar los datos de la tabla tienda_mes y tienda día, esto por motivos de inserción de información inicial
 */

  //Conexion a la Base de datos
  set_time_limit(0);
/*
  $link = mysql_connect("72.167.233.75","lecbdlogic","CX14e5cu10gic");
  mysql_select_db("lecbdlogic");
*/
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


?>

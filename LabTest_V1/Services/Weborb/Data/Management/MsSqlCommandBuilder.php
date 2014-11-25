<?php
require_once("ICommandBuilder.php");
require_once("SqlCommandOptions.php");
require_once("ITableMeta.php");

class MsSqlCommandBuilder implements ICommandBuilder
{
    public /*String*/ function Create( /*SqlCommandOptions*/ $findOptions, /*ITableMeta*/ $tableMeta )
	{	
	    /*String*/ $sqlQuery = $this->BuildSql( $findOptions, $tableMeta );
		
		foreach( $findOptions->Fields as $field => $value )
	       $sqlQuery = str_replace( "@" . $field, "'" . $value . "'", $sqlQuery );
		    	
	    return $sqlQuery;
	
	}

	private /*String*/ function BuildSql( /*SqlCommandOptions*/ $findOptions, /*ITableMeta*/ $tableMeta )
	{
	    /*String*/ $sqlQuery = "";
	
	    $sqlQuery .= "Select ";
	
	    if( count( $findOptions->Select ) > 0)
	    {
	        for( /*int*/ $i = 0; $i < count( $findOptions->Select ); $i++ )
	        {
	            if ( $i > 0 )
	                $sqlQuery .= " , ";
	
	            $sqlQuery .= $findOptions->Select[ i ] ;
	        }
	    }
	    else
	        $sqlQuery .= "*";
	
	    $sqlQuery .= " From [". $tableMeta->getTableName() ."] ";
	
	    /*String*/ $sqlConditions = $findOptions->Conditions;
	
	    foreach($findOptions->Fields as /*String*/ $field => $value )
	    {
	        $sqlConditions = str_replace( ":" . $field, "@" . $field, $sqlConditions );
	    }
	
	    if( $sqlConditions != "" )
	        $sqlQuery .= " Where " . $sqlConditions;
		
	    return $sqlQuery;
	}	
}
?>
<?php
require_once("DataMapper.php");

class QueryOptions
{
    public /*int*/ $PageSize;
    public /*bool*/ $IsMonitored = true;
    public /*bool*/ $IsPaged = false;
    public /*int*/ $Offset;
    public /*int*/ $Limit;
    public /*array of SortField*/ $SortFields = array();
    public /*array of string*/ $LoadRelations = array();
    public /*DataMapper*/ $Owner;

    public /*bool*/ function isPaged()
    {
        return ( $this->PageSize > 0 );
    }

    public function __construct( $offset = 0, $limit = 0, $monitored = true, $pageSize = 0)
    {
        $this->Offset = $offset;
        $this->Limit = $limit;
        $this->IsMonitored = $monitored;
        $this->PageSize = $pageSize;
        $this->IsPaged = $pageSize > 0;
    }
    
    public function createInstance( /*Hashtable*/ $options, /*DataMapper*/ $owner )
    {       		
        $this->Owner = $owner;
        
 		if(!is_array($options))
 		{
 			$options = (array) $options;
 		}
 		
    	if( array_key_exists( "PageSize", $options ) )
    	{
        	$this->PageSize = $options[ "PageSize" ];
        	$this->IsPaged = $this->PageSize > 0;
    	}

        if( array_key_exists( "Limit", $options ) )
            $this->Limit = $options[ "Limit" ];

        if( array_key_exists( "Monitored", $options ) )
            $this->IsMonitored = $options[ "Monitored" ];

        if( array_key_exists( "Offset", $options ) )
            $this->Offset = $options[ "Offset" ];
            
        if( $this->Offset > 0 )
          $this->Offset -= 1;

        if( array_key_exists( "Sort", $options ) )
        { 
        	if( is_array( $options ) )
        	{
	            foreach( $options[ "Sort" ] as /*string*/$sortField )
	            {
	                /*SortField*/ $field = new SortField();
	                /*int*/ $delimiter = stripos( $sortField, ":" );
	                
	                if( $delimiter != -1)
	                {
	                    /*string*/ $field->Name = substr( $sortField, 0, delimiter );
	                    /*string*/ $sortDirection = substr( $sortField, delimiter + 1 );
	
	                    if( strtolower( $sortDirection ) == "desc" )
	                         /*SortField*/ $field->IsAsc = false;
	                }
	                else
	                    /*SortField*/ $field->Name = $sortField;
	
	                $this->SortFields[] = $field;
	            }
        	}
        }

        if( array_key_exists( "LoadRelations", $options ) )
        {
        	if( is_array( $options[ "LoadRelations" ] ) )
            {
            	foreach( $options[ "LoadRelations" ] as /*String*/ $relationName )
                {
                	$this->LoadRelations[] = $relationName;
                }
            }
        }    	
    }
    
    public /*List<String>*/ function GetRelations( /*DataMapper*/ $dataMapper )
    {
    	/*List<String>*/ $relations = array();
    	
        foreach( /*List<String>*/ $this->LoadRelations as /*String*/$relation )
        {
            /*int*/$pointIndex = stripos( $relation, "." );

            if ( $pointIndex === false )
            {
                if( $dataMapper == $Owner )
                    $relations[] = $relation;
            }
            else
            {
                /*String*/$tableName = $dataMapper->getSafeName( substr( $relation, 0, pointIndex ) );
                /*String*/$tableNameWithoutSchema = $dataMapper->TableName;

                if( stripos( $tableNameWithoutSchema, "." ) != false )
                    $tableNameWithoutSchema = substr( $tableNameWithoutSchema, stripos( $tableNameWithoutSchema, "." ) + 1 );
                //tableName = dataMapper.GetSafeName(tableName);

                if( $tableName == $tableNameWithoutSchema )
                    $relations[] = substr( $relation, $pointIndex + 1 );
            }
        }

        return $relations;
    }
    
    public /*Hashtable*/ function Convert()
    {
        /*Hashtable*/ $options = array();

        $options[ "PageSize" ] = $this->PageSize;
        $options[ "Limit" ] = $this->Limit;
        $options[ "Monitored" ] = $this->IsMonitored;
        $options[ "Offset" ] = $this->Offset;
        $options[ "LoadRelations" ] = $this->LoadRelations;
        $options[ "Sort" ] = $this->SortFields;

        return $options;
    }    
}

class QueryOptionsSortField
{
    public $Name;
    public $IsAsc = true;
}
?>
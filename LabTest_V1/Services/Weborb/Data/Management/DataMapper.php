<?php
require_once("DomainObject.php");
require_once("QueryResult.php");
require_once(WebOrb. "V3Types" . DIRECTORY_SEPARATOR . "AckMessage.php");
require_once("DynamicMethod.php");
require_once("DataServiceClientRegistry.php");
require_once("ActiveQuery.php");
require_once("DataServiceClient.php");
require_once("IdentityMap.php");
require_once("ITableMeta.php");
require_once("IDataMapper.php");
require_once("QueryOptions.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

abstract class DataMapper implements ITableMeta, IDataMapper
{
	private $startTime;
	private $endTime;
	private /*IdentityMap*/ $identityMap;
	public abstract function getConnection();
	public abstract function getTableName();
	//public abstract /*String*/ function getSafeName($name);
	public abstract /*String*/ function getSafeParam($param, $isNullable, $databaseLink);
	public abstract /*DomainObject*/ function create(DomainObject $item);
    public abstract /*DomainObject*/ function update(DomainObject $item);
    public abstract /*DomainObject*/ function remove(DomainObject $item, $cascade);
    public abstract /*DomainObject*/ function save(DomainObject $item);
	public abstract /*DomainObject*/ function doLoad(&$row);
	protected abstract /*ArrayList*/ function fill( /*String*/ $command, /*int*/ $offset, /*int*/ $limit );
//	public abstract /*Hashtable*/ function getRelation( /*String*/ $tableName );
	public function __construct()
	{
		$this->getConnection();
	}
	public function getFill( /*String*/ $command, /*int*/ $offset, /*int*/ $limit )
	{
		return $this->fill( /*String*/ $command, /*int*/ $offset, /*int*/ $limit );
	}
	public function findAll( $options )
	{
		$this->startTime = microtime(true);
		/*String*/ $sqlQuery = $this->getFindAllSql();
        /*QueryOptions*/ $queryOptions = new QueryOptions( );
        $queryOptions->createInstance( $options, $this );

        $sqlQuery .= $this->getOrderBySqlPart( $queryOptions->SortFields );

        /*String*/ $queryId = AckMessage::uuid();

        if( $queryOptions->IsPaged || $queryOptions->IsMonitored )
            $this->registerCollection( $sqlQuery, $queryId, $queryOptions->PageSize, $queryOptions->IsMonitored );

        if( $queryOptions->IsPaged )
            return $this->getQueryPage( $queryId, 1, $queryOptions );

        /*List<TDomain>*/ $domainObjectList = array();

        $domainObjectList = $this->fill( $sqlQuery, $queryOptions->Offset, $queryOptions->Limit);
        $totalRows = count($domainObjectList);
		$activeQuery = new ActiveQuery( $sqlQuery, $_GET["clientid"], $totalRows, $queryOptions->IsMonitored, "");
		$queryResult = new QueryResult($activeQuery, 1);
		$queryResult->Result = $domainObjectList;
		$queryResult->TotalRows = $totalRows;
        for( /*int*/ $i = 0; $i < count( $domainObjectList ); $i++ )
        {
        	/*DomainObject*/ $domainObject = $domainObjectList[ $i ];

        	$this->loadRelations( $domainObject, $queryOptions );
        }

//        Log::log(LoggingConstants::MYDEBUG, "Time of execute: " . $allTime);
		return $queryResult;
	}

	public /*DomainObject*/ function findFirst(/*void*/)
	{
		/*QueryOptions*/ $queryOptions = new QueryOptions( 0, 1, false );
	    /*QueryResult*/ $queryResult = $this->findAll( $queryOptions->Convert() );
		/*DomainObject*/ $domainObject = $queryResult->Result[ 0 ];
	    return $domainObject;
	}

	public /*DomainObject*/ function findLast()
	{
		/*int*/ $rowCount = $this->getRowCountBySql( $this->getFindAllSql() );
		if ( $rowCount == 0 )
		    return null;

		/*QueryOptions*/ $queryOptions = new QueryOptions( $rowCount, 1, false );
		/*QueryResult*/ $queryResult = $this->findAll( $queryOptions->Convert() );

		 /*DomainObject*/ $domainObject = $queryResult->Result[ 0 ];

		return $domainObject;
	}



    private /*String*/ function getFindAllSql()
    {
        return "Select * From " . $this->getSafeName( $this->getTableName() );
    }

	public /*QueryResult*/ function findDynamic( /*String*/ $method, /*ArrayList*/ $args, /*QueryOptions*/ $options )
    {
    	/*QueryOptions*/ $queryOptions = "";

    	if( is_null( $options ) || $options == null )
        	$queryOptions = new QueryOptions();
        else
        	$queryOptions = $options;

        /*DynamicMethod*/ $dynamicMethod = new DynamicMethod( $method, $args );
        /*String*/ $queryId = AckMessage::uuid();

        /*ArrayList*/ $domainObjectList = array();
		/*String*/ $query = $this->getCommandBuilder()->Create( $dynamicMethod->CommandOptions, $this, $this->getConnection() );
        $query .= $this->getOrderBySqlPart( $queryOptions->SortFields );

        Log::log( LoggingConstants::DEBUG, "Executing dynamic method " . $method . "; SQL:" . $query );

        if( $queryOptions->IsMonitored || $queryOptions->IsPaged )
        {
            /*Hashtable*/ $filter = array();
			if( $dynamicMethod->CommandOptions->IsStrongCondition() )
				$filter = $dynamicMethod->CommandOptions->Fields;
			else
				$filter = array();

            $this->registerCollection( $query, $queryId, $queryOptions, $filter);
        }

        if( $queryOptions->IsPaged)
            return $this->getQueryPage( $queryId, $queryOptions->PageSize, $queryOptions );
//        var_dump($query);
//        Log::log(LoggingConstants::MYDEBUG, ob_get_contents());

        error_log( "SQL is " .$query, 0 );

		$domainObjectList = $this->fill( $query, $queryOptions->Offset, $queryOptions->Limit );

        $this->loadRelations( $domainObjectList, $queryOptions );

        if( count( $domainObjectList ) == 0 && ( $dynamicMethod->CommandOptions->CreateIfNotFound || $dynamicMethod->CommandOptions->InitializeIfNotFound ) )
        {
        	/*DomainObject*/ $domainObject = $this->doLoad( $dynamicMethod->CommandOptions->Fields );

            if( $dynamicMethod->CommandOptions->CreateIfNotFound )
                $this->create( $domainObject );

            $domainObjectList[] = $domainObject;
        }

        return $domainObjectList;//new QueryResult( $queryId, $queryOptions->IsMonitored, $domainObjectList );
    }

    public /*QueryResult*/ function findBySql( /*String*/ $sqlQuery, /*Hashtable*/ $options )
    {
        /*String*/ $queryId = AckMessage::uuid();
        /*QueryOptions*/ $queryOptions = new QueryOptions( );
        $queryOptions->createInstance( $options, $this );

        if ( $queryOptions->IsPaged || $queryOptions->IsMonitored )
            $this->registerCollection( $sqlQuery, $queryId, $queryOptions, $queryOptions->IsMonitored);

        if ( $queryOptions->IsPaged )
            return $this->getQueryPage( $queryId, 1, $queryOptions );

        /*ArrayList*/ $domainObjectList = $this->fill( $sqlQuery, $queryOptions->Offset, $queryOptions->Limit );
        $this->loadRelations( $domainObjectList, $queryOptions );

        $totalRows = count($domainObjectList);
		$activeQuery = new ActiveQuery( $sqlQuery, $_GET["clientid"], $totalRows, $queryOptions->IsMonitored, "");
		$queryResult = new QueryResult($activeQuery, 1);
		$queryResult->Result = $domainObjectList;
		$queryResult->TotalRows = $totalRows;

        return $queryResult;
    }

	public /*QueryResult*/ function getQueryPage( /*String*/ $queryId, /*int*/ $pageNumber, QueryOptions $queryOptions )
    {
    	/*ActiveQuery*/ $activeQuery = null;
        foreach( DataServiceClientRegistry::getClient()->MonitoredQueries as /*ActiveQuery*/ $item )
        {
        	if( $item->QueryId == $queryId  )
        		$activeQuery = $item;
        }

        if( $activeQuery == null )
            throw new Exception("Query with id \"" . $queryId . "\" not found");

        /*QueryResult*/ $queryResult = new QueryResult( $activeQuery, $pageNumber );
        /*ArrayList*/ $domainObjectList = array();

        $queryResult->TotalRows = $this->getRowCountBySql( $activeQuery->SqlQuery );

        if( $queryOptions->Limit > 0 )
        	$activeQuery->TotalRows = $queryResult->TotalRows;

        $domainObjectList = $this->fill( $activeQuery->SqlQuery, $queryResult->StartIndex, $queryResult->PageSize );
        $this->loadRelations( $domainObjectList, $queryOptions );
        $queryResult->Result = $domainObjectList;
        $allTime = microtime(true) - $this->startTime;
        Log::log(LoggingConstants::MYDEBUG, "Time of execute: " . $allTime);
        return $queryResult;
    }

    public /*void*/ function registerCollection( /*String*/ $sqlQuery, /*String*/ $collectionId, /*int*/ $pageSize, /*boolean*/ $monitored, /*Hashtable*/ $filter = array() )
    {
    	/*DataServiceClient*/ $client = DataServiceClientRegistry::getClient();

        if( !is_null( $client ) )
        	$client->MonitoredQueries[] = new ActiveQuery( $sqlQuery, $collectionId, $pageSize, $monitored, "DomainObject", $filter );
        else
            Log::log( LoggingConstants::INFO, "Unable to locate data service client. Collection cannot be monitored" );
        DataServiceClientRegistry::save();
    }

    protected abstract /*void*/ function loadRelations( /*DomainObject*/ $domainObject, /*QueryOptions*/ $queryOptions );

    protected /*DomainObject*/ function registerRecord( DomainObject $domainObject )
    {
        /*IdentityMap*/ $idMap = $this->getIdentityMap();

        if( !is_null( $idMap ) )
            $idMap->register( $domainObject );

        return $domainObject;
    }

    public /*boolean*/ function IsInQuery( /*DomainObject*/ $domainObject, /*ActiveQuery*/ $activeQuery )
    {
        if ( stripos( strtolower( $activeQuery->SqlQuery ), "where" ) == false )
            return true;

        if ( count( $activeQuery->Filter ) > 0 )
            return $domainObject->contains( $activeQuery->Filter );

        /*String*/ $dbCommand = prepareCheckInCommand( $domainObject, $activeQuery->SqlQuery );
        /*ResultSet*/ $result = mysql_query( $dbCommand, getConnection() );

       	return result.next();
    }

    protected static /*String*/ function modifyQueryForCheckIn( /*String*/ $originalQuery, /*String*/ $primaryKeyPart )
    {
        /*int*/ $insertIndex = stripos( strtolower( $originalQuery), "where" ) + 6;
        /*String*/ $modifiedQuery = substr( $originalQuery, 0, $insertIndex ) . "(" . $primaryKeyPart . ") And " . substr( $originalQuery, $insertIndex );

        return $modifiedQuery;
    }

    //protected abstract String function prepareCheckInCommand( /*DomainObject*/ $domainObject, /*String*/ $sqlQuery );

    protected /*String*/ function getOrderBySqlPart( /*ArrayList*/ $sortFields )
    {
        if ( count( $sortFields ) == 0)
            return "";

        /*String*/ $sql = "";

        $sql .= "Order By ";

        for( /*int*/ $i = 0; $i < count( $sortFields ); $i++ )
        {
            /*QueryOptions.SortField*/ $sortField = $sortFields[ i ];

            if( $i > 0 )
                $sql .= ",";

            $sql .= getSafeName( $sortField->Name );

            if ( !$sortField->IsAsc )
                $sql .= " Desc" ;
        }



        return $sql;
    }

    private /*IdentityMap*/ function getIdentityMap()
    {
        if ( is_null( $this->identityMap ) )
            $this->identityMap = DataServiceClientRegistry::getIdentityMap();

        return $this->identityMap;
    }
}
?>
<?php

class ActiveQuery
{
    /*string*/ public $SqlQuery;
    /*string*/ public $QueryId;
    /*Type*/ public $DomainObjectType;
    /*int*/ public $PageSize;
    /*bool*/ public $IsMonitored;
    /*int*/ public $TotalRows;
    /*Hashtable*/ public $Filter;

    public function ActiveQuery( /*String*/ $sqlQuery, /*String*/ $queryId, /*int*/ $pageSize, /*bool*/ $monitored, /*Type*/ $domainObjectType, /*Hashtable*/ $filter = NULL )
    {
    	$this->SqlQuery = $sqlQuery;
        $this->QueryId = $queryId;
        $this->DomainObjectType = $domainObjectType;
        $this->PageSize = $pageSize;
        $this->IsMonitored = $monitored;
        
        if( is_null( $filter ) )
        	$this->Filter = array();
        else
        	$this->Filter = $filter;
    }       
}

?>
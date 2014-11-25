<?php
require_once("ActiveQuery.php");

class QueryResult
{
	public /*object*/ $Result;
	public /*int*/ $StartIndex;
	public /*int*/ $TotalRows;
	public /*String*/ $QueryId;
	public /*bool*/ $IsMonitored;
	public /*int*/ $PageSize;
	public /*int*/ $PageNumber;
	public /*bool*/ $IsInitional;
	public /*bool*/ $IsPaged;

    public function __construct( /*ActiveQuery*/ $activeQuery, /*int*/ $pageNumber )
    {
        $this->QueryId = $activeQuery->QueryId;
        $this->PageSize = $activeQuery->PageSize;
        $this->IsMonitored = $activeQuery->IsMonitored;
        $this->PageNumber = $pageNumber;
        $this->StartIndex = $this->PageNumber * $this->PageSize - $this->PageSize;
        $this->IsInitional = $this->PageNumber == 1;
        $this->IsPaged = $this->PageSize > 0;
    }
}
?>
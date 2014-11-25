<?php
interface IcommandBuilder
{
	function Create( /*SqlCommandOptions*/ $findOptions, /*ITableMeta*/ $tableMeta );
}
?>
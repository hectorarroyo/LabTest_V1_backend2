<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLFindAllCodegenerator.php
 * Copyright (C) 2006-2007 Midnight Coders, LLC
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is WebORB Presentation Server (R) for PHP.
 *
 * The Initial Developer of the Original Code is Midnight Coders, LLC.
 * All Rights Reserved.
 ********************************************************************/
class WDMServerPHPDataMySQLFindAllCodegenerator extends WDMCodegenerator
{
protected $tableName;
	protected $tableMeta;

	public function __construct($tableName)
	{
		$this->tableName = $tableName;
	}
	protected function doGenerate()
	{
		$table = $this->tableName;
		$this->tableMeta = $this->meta->tables[$this->tableName];

		$this->writeText("

			protected  function fill( \$command, \$offset, \$limit )
			{
				\$resultList = array();

				if( \$limit != 0 )
					\$command = \$command.\" LIMIT \" . \$offset . \" , \" . \$limit;

				\$result = mysql_query(\$command);

					while( \$row = mysql_fetch_assoc( \$result ) )
					{
						\$item = new " . $this->meta->getClassName($this->tableName) . "();
						");

		foreach ($this->tableMeta->Columns as $column)
		{
			$this->writeText("
  				\$item->set".$this->meta->getPropertyName($table,$column->name)."(\$row[\"".$column->name."\"]);");
		}

		$this->writeText("
						\$this->registerRecord(\$item);
						\$resultList[] = \$item ;
					}

				  return \$resultList;
			}

			protected function loadRelations( \$domainObject, \$queryOptions )
		    {"
		);

		foreach ($this->tableMeta->Relations as $relation)
		{
			if ($relation->Type == RelationType::Child)
			{
				$relatedName = $this->meta->getChildProperty( $table, $relation->RelatedTableName, $relation->ForeignKey, false );
				$this->writeText("

		    	foreach( \$queryOptions->getRelations(\$this) as /*String*/ \$relationName )
		    	{
		            if( \$relationName == \"".$relatedName."\" )
		            {
						require_once(WebOrbServicesPath. \"" . str_replace('.', '/', $this->meta->getServerNamespace()) . "/" . $this->meta->getClassName($relation->RelatedTableName)."DataMapper.php\");
		            	\$dataMapper = new ".$this->meta->getClassName($relation->RelatedTableName)."DataMapper();

		                \$domainObject->set".$relatedName."( \$dataMapper->findBy".$this->meta->getParentProperty( $table, $relation->TableName, $relation->ForeignKey, false )."( \$domainObject, \$queryOptions ) );

		            }
		        }

				");
			}

		}
		$this->writeText("
		}");
	}
}
?>
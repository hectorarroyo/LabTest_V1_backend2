<?php
/*******************************************************************
 * WDMServerPHPDomainCodegenerator.php
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
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");
class WDMServerPHPDomainCodegenerator extends WDMCodegenerator
{
	protected $tablePrefix;
	protected $tableName;
	protected $className;
	protected $isDataMapper;

	public function __construct($tablePrefix,$tableName)
	{
		$this->tablePrefix = $tablePrefix;
		$this->tableName = $tableName;
	}
	protected function doGenerate()
	{

		$this->className = $this->meta->getClassName($this->tableName);
		$this->tableMeta = $this->meta->tables[$this->tableName];
		$this->varName = $this->meta->getFunctionParameter($this->tableName);

		if ($this->tablePrefix != "_")
		{
			$this->writeStartFile($this->className.".php");
			$this->writeText("
			<?php
				require_once(\"Codegen/_".$this->className.".php\");

				class ".$this->className." extends _".$this->className."
				{
				}
			?>");
			$this->writeEndFile();

			// data mapper file
			$this->writeStartFile($this->className."DataMapper.php");
			$this->writeText("
			<?php
				require_once(\"Codegen/_".$this->className."DataMapper.php\");
				class " . $this->className."DataMapper extends _".$this->className."DataMapper
				{
				}
			?>");
			$this->writeEndFile();
		}
		else
		{
			// --------------------------------- domain class file
			$table = $this->tableName;
			$this->writeStartFile($this->tablePrefix.$this->className.".php");
			$this->writeText("
			<?php
			require_once(WebOrbServicesPath. \"Weborb/Data/Management/DomainObject.php\");

			class ". $this->tablePrefix.$this->className ." extends DomainObject
			{
				");

			$IsRelated = false;
			$RelatedTableName = "";
			$TableName = "";
			$getSet = "";
			$initRelatedObjects = "";
			$require_onceRelatedObjectsClasses = "";
//			$relatedFields = array();

			foreach ($this->tableMeta->Columns as $column)
			{
				$IsRelated = false;
				foreach ($this->tableMeta->Relations as $relation)
				if ($relation->Type == RelationType::Parent)
				{
					foreach ($relation->Columns as $relatedColumn)
					if ($column->name == $relatedColumn->ColumnName && !$column->isNullable)
					{
						$RelatedTableName = $this->meta->getParentProperty($table, $relation->RelatedTableName, $relation->ForeignKey, false);
						$RelatedColumnName = $relatedColumn->RelatedColumnName;
						$TableName = $relation->RelatedTableName;
						$IsRelated = true;
						break;
					}
				}

				if ($IsRelated)
				{

					$this->writeText("public $".$RelatedTableName.";
				");

					$require_onceRelatedObjectsClasses = $require_onceRelatedObjectsClasses."require_once(WebOrbServicesPath. \"" .  str_replace('.', '/', $this->meta->getServerNamespace()). "/" . $this->meta->getClassName($TableName) . ".php\");\n";

					$initRelatedObjects = $initRelatedObjects." \$this->".$RelatedTableName." = new ".$TableName."();";
					$getSet = $getSet."public function get".$this->meta->getPropertyName($table,$column->name)."()
				{
					if (!is_null(\$this->".$RelatedTableName."))
						return \$this->".$RelatedTableName."->get".$this->meta->getPropertyName($table,$RelatedColumnName)."();
				}
				public function set".$this->meta->getPropertyName($table,$column->name)."(\$value)
				{
					if (is_null(\$this->".$RelatedTableName."))
						\$this->".$RelatedTableName." = new ".$TableName."();
					\$this->".$RelatedTableName."->set".$this->meta->getPropertyName($table,$RelatedColumnName)."(\$value);
				}
				";
				}
				else
				{
					if ($column->dataTypeInfo->dataType == "datetime")
					{
						$this->writeText("public $".$this->meta->getPropertyName($table,$column->name).";
				");
				$getSet = $getSet."public function get".$this->meta->getPropertyName($table,$column->name)."()
				{
					if(\$this->".$this->meta->getPropertyName($table,$column->name)." == null)
					{
						require_once( WebOrb . 'Util" . DIRECTORY_SEPARATOR . "ORBDateTime.php');
						return new ORBDateTime(\null, date_timezone_get() );
					}
					return \$this->".$this->meta->getPropertyName($table,$column->name).";
				}
				public function set".$this->meta->getPropertyName($table,$column->name)."(\$value)
				{
					require_once( WebOrb . 'Util" . DIRECTORY_SEPARATOR . "ORBDateTime.php');
					if(\$value instanceof ORBDateTime)
						\$this->".$this->meta->getPropertyName($table,$column->name)." = \$value;
					else
					{
						\$orbDateTime = new ORBDateTime(\$value, date_timezone_get() );
						\$this->".$this->meta->getPropertyName($table,$column->name)." = \$orbDateTime;
					}
				}
				";
					}
					else
					{
						$this->writeText("public $".$this->meta->getPropertyName($table,$column->name).";
				");
						$getSet = $getSet."public function get".$this->meta->getPropertyName($table,$column->name)."()
				{
					return \$this->".$this->meta->getPropertyName($table,$column->name).";
				}
				public function set".$this->meta->getPropertyName($table,$column->name)."(\$value)
				{
					\$this->".$this->meta->getPropertyName($table,$column->name)." = \$value;
				}
				";
					}
				}
			}

			$this->writeText($getSet);

			foreach ($this->tableMeta->Relations as $relation)
			if ($relation->Type == RelationType::Child)
			{
				$relatedName = $this->meta->getChildProperty($table, $relation->RelatedTableName, $relation->ForeignKey, false);
				$relatedVarName = $this->meta->getFunctionParameter($this->meta->getChildProperty($table, $relation->RelatedTableName, $relation->ForeignKey, false));
				$this->writeText("
				public \$".$relatedVarName.";
				public function get".$relatedName."()
				{
					return \$this->".$relatedVarName.";
				}
				public function set".$relatedName."(&\$value)
				{
					\$this->".$relatedVarName." = \$value;
				}
				public function add".$relatedName."Item(".$this->meta->getClassName($relation->RelatedTableName)." &\$childObject)
				{
				  \$childObject->".$this->meta->getChildProperty($relation->RelatedTableName, $this->tableName, $relation->ForeignKey, false)." = &\$this;
				  \$this->".$relatedVarName."[] = \$childObject;
				  return \$childObject;
				}

				");
			}

			$this->writeText("
				public function __construct()
				{
					parent::__construct();
					".$require_onceRelatedObjectsClasses."					".$initRelatedObjects."
				}
			");
			$this->writeText("
				public function getUri()
				{
					\$uri = \"".$this->meta->getUserDataModel()->Name.".".$this->className."\"");
			$keys = "";
			foreach ($this->tableMeta->Columns as $column)
			{
				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$this->writeText(".\$this->".$this->meta->getPropertyName($table,$column->name));
				}
			}

			$this->writeText(";
					return \$uri;
				}");

			$this->writeText("
				public function contains(&\$fields)
				{
					\$matchCount = 0;
				");

			foreach ($this->tableMeta->Columns as $column)
			{
				$this->writeText("
					if (array_key_exists(\"".$column->name."\", \$fields))
					{
						if(\$fields[\"".$column->name."\"] != \$this->".$column->name.")
						   	return false;
						else
						{
							\$matchCount++;
							if(\$matchCount == sizeof(\$fields))
						   		return true;
						}
					}
					");
			}

			$this->writeText("
					\$result = (\$matchCount == sizeof(\$fields));
					return \$result;
				}");

			$this->writeText("
				public function extractSingleObject()
				{
					$".$this->varName." = new ".$this->className."();
				");

			foreach ($this->tableMeta->Columns as $column)
			{
				$this->writeText("
	  				\$".$this->varName."->set".$this->meta->getPropertyName($table,$column->name)."(\$this->get".$column->name."());");
			}
			$this->writeText("
					\$".$this->varName."->ActiveRecordUID = \$this->ActiveRecordUID;
					return \$".$this->varName.";
				}");

			$this->writeText("
			}
			?>");
		 $this->writeEndFile(); // ---------------------- data mapper file
		 $this->writeStartFile('_' . $this->className."DataMapper.php");
		 	$this->generatePart(new WDMServerPHPDataCodegenerator($this->tablePrefix,$this->tableName));
		 $this->writeEndFile();
		}
	}
}
?>

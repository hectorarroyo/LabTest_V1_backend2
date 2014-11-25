<?php
/*******************************************************************
 * WDMClientUIUnitTestCreateCodegenerator.php
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
class WDMClientUIUnitTestCreateCodegenerator extends WDMCodegenerator
{
	protected $tableName;
	protected /*TableMeta*/ $tableMeta;
	
	public function __construct(/*string*/ $tableName)
	{
		$this->tableName = $tableName;
	}
		
	protected function doGenerate()
	{
		$this->tableMeta = $this->meta->tables[$this->tableName];
		
		$className = $this->meta->getClassName($this->tableName);		
		$this->writeStartFile("UTest".$className."Create.as");
		$this->writeText("      package UI.UnitTest
      {
         import weborb.utest.*;
         import ".$this->meta->getClientNamespace().".*;
         import mx.rpc.Responder;
         import weborb.data.ActiveRecord;
         import flash.utils.ByteArray;
         import mx.utils.UIDUtil;

         public class UTest".$className."Create extends UnitTest
         {
            protected var ".$this->meta->getFunctionParameter($className).":".$className.";

            public function UTest".$className."Create (name:String = \"".$className." - Create\")
            {
              super(name);
            }

            protected override function onInitialize():void
            {
               ".$this->meta->getFunctionParameter($className)." = new ".$className."();\n");


		foreach ($this->tableMeta->Columns as $column)
		{
			if (!$column->isAutoIncrement)
			{
				$columnName = $column->name;
				foreach ($this->tableMeta->Relations as $relation)
				{
					foreach ($relation->Columns as $relationColumns)
					{
						if ($relationColumns->ColumnName == $columnName)
							$columnName = null;
					}
				}
				if ($columnName)
					$this->writeText("
               ".$this->meta->getFunctionParameter($className).".".$this->meta->getPropertyName($this->tableName,$columnName)." = ".$this->meta->getPrimitiveValue($column->dataTypeInfo->dataType).";");				
			}
		}

	foreach ($this->tableMeta->Relations as $relation)
	{
		if ($relation->Type == RelationType::Parent)
		{
			$relationName = $this->meta->getParentProperty($this->tableName, $relation->RelatedTableName, $relation->ForeignKey, false);
			
			$this->writeText("
        ActiveRecords.".$this->meta->getClassName($relation->RelatedTableName).".findFirst().addResponder(
              new Responder(on".$relationName."Received,onFault));");
		}
	}
	$this->writeText("
            }\n");

	$count = 0;
	foreach ($this->tableMeta->Relations as $relation)
	{
		if ($relation->Type == RelationType::Parent)
			$count++;
	}
		$this->writeText("
            protected override function getPartsCount():int
            {
              return ".$count.";
            }
		
            protected override function onExecute():void
            {
              ".$this->meta->getFunctionParameter($className).".save().addResponder(new Responder(onSaved,onFault));
            }

            protected virtual function onSaved(activeRecord:ActiveRecord):void
            {
              raiseOnResult();
            }\n");
	
	foreach ($this->tableMeta->Relations as $relation)
	{
		if ($relation->Type == RelationType::Parent)
		{
			$relationName = $this->meta->getParentProperty($this->tableName, $relation->RelatedTableName, $relation->ForeignKey, false);

			$this->writeText("
            protected function on".$relationName."Received(".$this->meta->getFunctionParameter($relationName).":".$this->meta->getClassName($relation->RelatedTableName)."):void
            {
              ".$this->meta->getFunctionParameter($className).".".$relationName." = ".$this->meta->getFunctionParameter($relationName).";

              onPartReceived(".$this->meta->getFunctionParameter($relationName).");
            }");				
		}
	}
	
	$this->writeText("
         }
      }");
		
		$this->writeEndFile();		
	}
}
?>
<?php
/*******************************************************************
 * WDMClientDomainCodegenActiveRecordCodegenerator.php
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
require_once(WebOrbServicesPath . "Weborb/Management/Codegen/WDM/WDMCodegenerator.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

class WDMClientDomainCodegenActiveRecordCodegenerator extends WDMCodegenerator
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
		$table = $this->tableName;
		
		$this->writeStartFile("_".$className.".as");
		
		$this->writeText("
   package ".$this->meta->getClientNamespace().".Codegen
   {
      import weborb.data.*;
      import ".$this->meta->getClientNamespace().".*;
      import mx.collections.ArrayCollection;
      import flash.utils.ByteArray;

      [Bindable]
      public dynamic class _".$className." extends ActiveRecord
      {

        public function get ActiveRecordUID():String
        {
          return _activeRecordId;
        }

        public function set ActiveRecordUID(value:String):void
        {
          _activeRecordId = value;
        }

         private var _uri:String = null;\n");
		
		foreach ($this->tableMeta->Columns as $column)
		{
			$this->writeText("
         protected var _".$this->meta->getFunctionParameter($this->meta->getPropertyName($table,$column->name)).":".$this->meta->getASDataType($column->dataTypeInfo->dataType)."");
			if ($this->meta->getASDataType($column->dataTypeInfo->dataType) == "Number")
				$this->writeText(" = 0");
			$this->writeText(";");
		}

		foreach ($this->tableMeta->Relations as $relation)
		{
			if ($relation->Type == RelationType::Parent)
			{
				$fieldName = $this->meta->getParentProperty($table, $relation->RelatedTableName, $relation->ForeignKey, true);

				$relatedFields[] = $fieldName;
				$this->writeText("\n
         // parent tables
         internal var _". $fieldName .":".$this->meta->getClassName($relation->RelatedTableName)."");
				
				foreach ($relation->Columns as $relatedColumn)
				{
					foreach ($this->tableMeta->Columns as $column)
					{
						if ($column->name == $relatedColumn->ColumnName && !$column->isNullable)
						{
							$this->writeText(" = new ".$this->meta->getClassName($relation->RelatedTableName)."()");
							break;
						}
					}
				}

				$this->writeText(";");
			}
		}		

		$this->writeLine("");

		foreach ($this->tableMeta->Columns as $column)
		{
			$attributeName = $column->name;
			$property = $this->meta->getPropertyName($table, $column->name);

			$isRelationColumn = false;
			
			foreach ($this->tableMeta->Relations as $relation)
			{
				if ($relation->Type == "Parent")
				{
					foreach ($relation->Columns as $relationColumns)
					{
						if ($relationColumns->ColumnName == $attributeName)
						{
							$isRelationColumn = true;
							break;						
						}
					}
				}
			}
			if (!$isRelationColumn)
			{				
				$this->writeText("
         public function get ".$property."():".$this->meta->getASDataType($column->dataTypeInfo->dataType)."
         {
           return _".$this->meta->getFunctionParameter($this->meta->getPropertyName($table, $attributeName)).";
         }

         public function set ".$property."(value:".$this->meta->getASDataType($column->dataTypeInfo->dataType)."):void
         {");
				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$this->writeText("
            _isPrimaryKeyAffected = true;
            _uri = null;

            if(IsLoaded || IsLoading)
            {
              trace(\"Critical error: attempt to modify primary key in initialized object \" + getURI());
              return;
            }");
				}
			
				$this->writeText("
            _".$this->meta->getFunctionParameter($this->meta->getPropertyName($table,$column->name))." = value;");
			
				$this->writeText("
         }\n");				
			}
			else
			{
				
				$this->writeText("
         public function get ".$property."():".$this->meta->getASDataType($column->dataTypeInfo->dataType)."
         {");
				
			foreach ($this->tableMeta->Relations as $relation)
			{
				$count = 0;
				foreach ($relation->Columns as $relationColumns)
				{
					$count++;
					if ($relationColumns->ColumnName == $attributeName)
					{
						$pkFieldPosition = $count;
						$parentTableName = $relation->RelatedTableName;
						$parentClassName = $this->meta->getClassName($parentTableName);
						$parentProperty = "_".$this->meta->getParentProperty($table,$parentTableName,$relation->ForeignKey,true)."";
						
						$this->writeText("
             if(".$parentProperty." != null)
                return ".$parentProperty.".".$this->meta->getPropertyName($parentTableName, $relationColumns->RelatedColumnName).";");
					}
				}
			}
            	$this->writeText("\n
            return undefined;
         }\n");
            	
            	$this->writeText("
         protected function set ".$property."(value:".$this->meta->getASDataType($column->dataTypeInfo->dataType)."):void
         {");

			foreach ($this->tableMeta->Relations as $relation)
			{
				$count = 0;
				foreach ($relation->Columns as $relationColumns)
				{
					$count++;
					if ($relationColumns->ColumnName == $attributeName)
					{
						$pkFieldPosition = $count;
						$parentTableName = $relation->RelatedTableName;
						$parentClassName = $this->meta->getClassName($parentTableName);
						$parentProperty = "_".$this->meta->getParentProperty($table,$parentTableName,$relation->ForeignKey,true)."";
						
						$this->writeText("
             if(".$parentProperty." == null)
                ".$parentProperty." = new ".$parentClassName."();
           
		  ".$parentProperty.".".$this->meta->getPropertyName($parentTableName, $relationColumns->RelatedColumnName)." = value;");
					}
				}
			}            	
            	
            	if ($column->keyType == ColumnKeyType::PRIMARY)
				{            	
            	$this->writeText("
           _isPrimaryKeyAffected = true;
           _uri = null;");
				}
				$this->writeText("
         }\n");
			
			}
		}

	 	foreach ($this->tableMeta->Relations as $relation)
		{
			if ($relation->Type == RelationType::Parent)
			{
				$key = $relation->ForeignKey;
				$parentTable = $relation->RelatedTableName;
				$parentClass = $this->meta->getClassName($parentTable);
				$varName = "_".$this->meta->getParentProperty($table,$parentTable,$key,true)."";
				$relatedProperty = $this->meta->getParentProperty($table, $parentTable, $key, false);

				$this->writeText("
         public function get ".$relatedProperty."():".$parentClass."
         {
           if(IsLoaded && ");
				foreach ($relation->Columns as $relatedColumn)
				{
					foreach ($this->tableMeta->Columns as $column)
					{
						if ($column->name == $relatedColumn->ColumnName && $column->isNullable)
						{
							$this->writeText("".$varName." && ");
							break;
						}
					}
				}

				$this->writeText("!(".$varName.".IsLoaded || ".$varName.".IsLoading))
           {

             var oldValue:ActiveRecord = ".$varName.";

             ".$varName." = DataMapperRegistry.Instance.".$parentClass.".load(".$varName.");

             if(oldValue != ".$varName.")
               onParentChanged(oldValue, ".$varName.");          

           }

           return ".$varName.";
         }

         public function set ".$this->meta->getParentProperty($table, $parentTable, $key, false)."(value:".$this->meta->getClassName($relation->RelatedTableName)."):void
         {
            if( value != null )
            {
               var oldValue:ActiveRecord = ".$varName.";
          	
               ".$varName." = ".$this->meta->getClassName($relation->RelatedTableName)."(IdentityMap.register( value ));

               if(oldValue != ".$varName.")
                 onParentChanged(oldValue, ".$varName.");
            }
            else
              ".$varName." = null;
         }");
				
			}
		}
		

		
			foreach ($this->tableMeta->Relations as $relation)
			{

				if ($relation->Type == RelationType::Child)
				{
					$childTable = $relation->RelatedTableName;
					$fk = $relation->ForeignKey;
					if ($relation->IsOneToMany)
					{
            			$varName = "_".$this->meta->getChildProperty($table,$relation->RelatedTableName,$fk,true)."";
            			$hiddenProperty = $this->meta->getChildProperty($table,$relation->RelatedTableName,$fk,true);
            			
						$this->writeText("\n
            // one to many relation
            protected var ".$varName.":ActiveCollection;
            
            public function get ".$this->meta->getChildProperty($table,$childTable,$fk,false)."():ActiveCollection
            {
              ".$varName." = onChildRelationRequest(\"".$hiddenProperty."\",".$varName.");
              
              return ".$varName.";
            }");
					}
					else
					{
						$varName = "_".$this->meta->getChildProperty($table,$relation->RelatedTableName,$fk,true)."";
						           			
						$this->writeText("
            // one to one relation
            protected var ".$varName.":".$this->meta->getClassName($relation->RelatedTableName).";
            
            public function get ".$this->meta->getChildProperty($table,$relation->RelatedTableName,$fk,false)."():".$this->meta->getClassName($relation->RelatedTableName)."
            {

               if(IsLoaded && ".$varName." == null)
               {
                 ".$varName." = DataMapperRegistry.Instance.".$this->meta->getClassName($relation->RelatedTableName).".findByPrimaryKey(
");
						
			$count = 0;
			foreach ($this->tableMeta->Columns as $column)
			{
				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$count++;
					if ($count == 1)					
						$this->writeText("".$column->name."");
					else
						$this->writeText(", ".$column->name."");					
				}
			}			
						
			$this->writeText(");
                   ".$varName."._parent".$className." = ".$className."( this );
               }

               return ".$varName.";
            }
            
            public function set ".$this->meta->getChildProperty($table,$relation->RelatedTableName,$fk,false)."(value:".$this->meta->getClassName($relation->RelatedTableName)."):void
            {
              ".$varName." = value;
              ".$varName."._parent".$className." = ".$className."(this);
            }
");
					
					}
				}				
			}

		$countParentRelation = 0;
		foreach ($this->tableMeta->Relations as $relation)
		{
			if ($relation->Type == RelationType::Parent)
				$countParentRelation++;
		}
		
		if (count($countParentRelation) > 0)
		{
			$this->writeText("\n
        protected override function onDirtyChanged():void
        {");
          foreach ($this->tableMeta->Relations as $relation)
          {
          	if ($relation->Type == RelationType::Parent)
          	{
	          	$key = $relation->ForeignKey;
          		$parentTable = $relation->RelatedTableName;
          		if(in_array($this->meta->getParentProperty($table,$parentTable,$key,false),$relatedFields))
          			continue;
          		$this->writeText("
            if(".$this->meta->getParentProperty($table,$parentTable,$key,false)." != null)
              ".$this->meta->getParentProperty($table,$parentTable,$key,false).".onChildChanged(this);");
          	}
          }

          $this->writeText("
        }\n");

		}
		
		$this->writeText("
        public override function extractRelevant(cascade:Boolean = false):Object
        {
          var object:".$className." = new ".$className."();\n");
			
			foreach ($this->tableMeta->Columns as $column)
			{
				$property = $this->meta->getPropertyName($this->tableName, $column->name);
				$this->writeText("
              object.".$property." = this.".$property.";");
			}
			
			$countChildRelation = 0;
		    foreach ($this->tableMeta->Relations as $relation)
            {
          		if ($relation->Type == RelationType::Child)
					$countChildRelation++;
            }
            
            if ($countChildRelation > 0)
            {
            	$this->writeText("\n
              if(cascade)
              {");
            	
	            foreach ($this->tableMeta->Relations as $relation)
	            {
	          		if ($relation->Type == RelationType::Child)
	          		{
	      		    	$fk = $relation->ForeignKey;
          				$childTable = $relation->RelatedTableName;
          				
          				$this->writeText("
                  for each(var ".$this->meta->getFunctionParameter($relation->RelatedTableName).":".$this->meta->getClassName($relation->RelatedTableName)." in _".$this->meta->getChildProperty($this->tableName,$childTable,$fk,true).")
                  {
                    if(".$this->meta->getFunctionParameter($relation->RelatedTableName).".IsDirty)
                    {
                       var ".$this->meta->getFunctionParameter($relation->RelatedTableName)."Extract:Object = ".$this->meta->getFunctionParameter($relation->RelatedTableName).".extractRelevant(true);
                           ".$this->meta->getFunctionParameter($relation->RelatedTableName)."Extract._".$this->meta->getParentProperty($childTable,$this->tableName,$fk,true)." = object;

                       object.".$this->meta->getChildProperty($this->tableName,$childTable,$fk,false).".addItem(".$this->meta->getFunctionParameter($relation->RelatedTableName)."Extract);
                    }
                  }\n");
	          		}
	            }
					
            	$this->writeText("
              }\n");
            }
            
            $this->writeText("
         object.ActiveRecordUID = this.ActiveRecordUID;
         
         return object;
       }\n");

            if ($countChildRelation > 0)
            {
				$this->writeText("
          public override function extractChilds():Array
          {
             var childs:Array = new Array();\n");
	          			            	
            	foreach ($this->tableMeta->Relations as $relation)
	            {
	          		if ($relation->Type == RelationType::Child)
	          		{
	          			$hiddenProperty = $this->meta->getChildProperty($this->tableName,$relation->RelatedTableName,$relation->ForeignKey,true);
	          			$this->writeText("\n
             if(this[\"".$hiddenProperty."\"])
             {
               for each(var ".$this->meta->getFunctionParameter($relation->RelatedTableName).":ActiveRecord in this[\"".$hiddenProperty."\"] as Array)
                  childs.push(".$this->meta->getFunctionParameter($relation->RelatedTableName).");
             }");

	          		}
	            }
	            
	            $this->writeText("\n
             return childs;
          }\n");
            }            
          
			$this->writeText("
          public override function applyFields(object:Object):void
          {");
			
			foreach ($this->tableMeta->Columns as $column)
			{
				$property = $this->meta->getPropertyName($this->tableName, $column->name);

				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$this->writeText("
             if(!IsPrimaryKeyInitialized)
               ".$property." = object[\"".$property."\"];\n");
				}
				else
				{
                	$this->writeText("
             ".$property." = object[\"".$property."\"];");					
				}
				
			}			
			
          foreach ($this->tableMeta->Relations as $relation)
          {
          	if ($relation->Type == RelationType::Parent)
          	{
          		$parentTable = $relation->RelatedTableName;
          		if (in_array($this->meta->getParentProperty($this->tableName,$parentTable,$relation->ForeignKey,false), $relatedFields))
          			continue;
          		$this->writeText("\n
             ".$this->meta->getParentProperty($this->tableName,$parentTable,$relation->ForeignKey,false)." = object.".$this->meta->getParentProperty($this->tableName,$parentTable,$relation->ForeignKey,false).";");
          	}
          }
			
			$this->writeText("\n
             _uri = null;
             _isPrimaryKeyAffected = true;
             IsDirty = false;
          }\n");
			
		$this->writeText("
        protected override function get dataMapper():DataMapper
        {
          return DataMapperRegistry.Instance.".$className.";
        }
        
        public override function getURI():String
        {

          if(_uri == null)
          {
             _uri = \"".$this->meta->getCurrentDatabase().".".$this->tableName."\"");

			foreach ($this->tableMeta->Columns as $column)
			{
				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$this->writeText(" + \".\" + ".$this->meta->getPropertyName($this->tableName, $column->name).".toString()");	
				}
			}

		$this->writeText(";
          }
           
          return _uri;
        }\n");
		

		$this->writeText("
      }");//public dynamic class
		
		$this->writeText("
   }");//package
		
		$this->writeEndFile();		
	}	
}
?>
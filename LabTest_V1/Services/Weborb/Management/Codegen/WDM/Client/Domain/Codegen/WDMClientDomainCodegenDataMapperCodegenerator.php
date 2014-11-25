<?php
/*******************************************************************
 * WDMClientDomainCodegenDataMapperCodegenerator.php
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

class WDMClientDomainCodegenDataMapperCodegenerator extends WDMCodegenerator
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
		
		$this->writeStartFile("_".$className."DataMapper.as");
		
		$this->writeText("
      package ".$this->meta->getClientNamespace().".Codegen
      {
        import weborb.data.*;

        import mx.rpc.AsyncToken;
        import mx.rpc.Responder;
        import mx.rpc.events.ResultEvent;
        import mx.rpc.remoting.RemoteObject;

        import ".$this->meta->getClientNamespace().".".$className.";
        import ".$this->meta->getClientNamespace().".DataMapperRegistry;
      
        public dynamic class _".$className."DataMapper extends DataMapper
        {
        
          public override function createActiveRecordInstance():ActiveRecord
          {
            return new ".$className."();
          }
        
          protected override function get RemoteClassName():String
          {
            return \"".$this->meta->getServerNamespace().".".$className."DataMapper\";
          }
          
          public function load(".$this->meta->getFunctionParameter($className).":".$className.", responder:Responder = null):".$className."
          {
              if(!".$this->meta->getFunctionParameter($className).".IsPrimaryKeyInitialized)
          	    throw new Error(\"Record can be loaded only with initialized primary key\");
          
              if(IdentityMap.exists(".$this->meta->getFunctionParameter($className).".getURI()))
              {
                ".$this->meta->getFunctionParameter($className)." = ".$className."(IdentityMap.extract(".$this->meta->getFunctionParameter($className).".getURI()));
                
                if(".$this->meta->getFunctionParameter($className).".IsLoaded || ".$this->meta->getFunctionParameter($className).".IsLoading)
                  return ".$this->meta->getFunctionParameter($className).";
              } 
              else
               IdentityMap.add(".$this->meta->getFunctionParameter($className).");

              var asyncToken:AsyncToken = new DatabaseAsyncToken(createRemoteObject().findByPrimaryKey(");
			$count = 0;
			foreach ($this->tableMeta->Columns as $column)
			{
				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$count++;
					if ($count == 1)
						$this->writeText("".$this->meta->getFunctionParameter($className).".".$this->meta->getPropertyName($this->tableName,$column->name)."");
					else
						$this->writeText(",".$this->meta->getFunctionParameter($className).".".$this->meta->getPropertyName($this->tableName,$column->name)."");
				}
			}
		
			$this->writeText("),null,".$this->meta->getFunctionParameter($className).");

              return ".$this->meta->getFunctionParameter($className).";
           }\n");
		
			$this->writeText("
           public function findByPrimaryKey(");
			$count = 0;
			foreach ($this->tableMeta->Columns as $column)
			{
				if ($column->keyType == ColumnKeyType::PRIMARY)
				{
					$count++;
					if ($count == 1)					
						$this->writeText("".$this->meta->getFunctionParameter($column->name).":".$this->meta->getASDataType($column->dataTypeInfo->dataType)."");
					else
						$this->writeText(", ".$this->meta->getFunctionParameter($column->name).":".$this->meta->getASDataType($column->dataTypeInfo->dataType)."");					
				}
			}			
			
			$this->writeText("):".$className."
           {
              var activeRecord:".$className." = new ".$className."();\n");
			
			foreach ($this->tableMeta->Columns as $column)
			{
				if ($column->keyType == ColumnKeyType::PRIMARY)
					$this->writeText("
              activeRecord.".$this->meta->getPropertyName($this->tableName,$column->name)." = ".$this->meta->getFunctionParameter($column->name).";");
			}
		
			$this->writeText("\n
              return load(activeRecord);
           }
");

		$this->writeText("
           public override function loadChildRelation(activeRecord:ActiveRecord,relationName:String, activeCollection:ActiveCollection):void
           {
              var item:".$className." = ".$className."(activeRecord);\n");
		
          foreach ($this->tableMeta->Relations as $relation)
          {
        		if ($relation->Type == RelationType::Child)
        		{
    		    	$fk = $relation->ForeignKey;
       				$childTable = $relation->RelatedTableName;
       				$hiddenProperty = $this->meta->getChildProperty($this->tableName,$relation->RelatedTableName,$fk,true);
       				$dynamicFunction = "findBy";
       				$count = 0;
       				foreach ($relation->Columns as $column)
       				{
       					$count++;
       					if ($count == 1)
	       					$dynamicFunction.=$column->RelatedColumnName;
	       				else
	       					$dynamicFunction.="And".$column->RelatedColumnName."";
       				}
       				$dynamicFunction.="(";
       				
       				$count = 0;
       				foreach ($this->tableMeta->Columns as $column)
					{
						if ($column->keyType == ColumnKeyType::PRIMARY)
						{
							$count++;
							if ($count == 1)
								$dynamicFunction.="item.".$this->meta->getPropertyName($this->tableName,$column->name)."";
							else
								$dynamicFunction.=", item.".$this->meta->getPropertyName($this->tableName,$column->name)."";
						}
					}
       				
       				$dynamicFunction.=", activeCollection, getRelationQueryOptions(relationName))";
       				$this->writeText("
              if(relationName == \"".$hiddenProperty."\")
              {
   
                 DataMapperRegistry.Instance.".$this->meta->getClassName($childTable).".".$dynamicFunction.";
                
                 return;
              }
           ");
           }
          }
			
	
		$this->writeText("
           }
        }  
      }");		

		$this->writeEndFile();		
	}
}
?>
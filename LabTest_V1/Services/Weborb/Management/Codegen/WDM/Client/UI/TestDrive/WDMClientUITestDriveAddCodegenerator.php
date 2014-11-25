<?php
/*******************************************************************
 * WDMClientUITestDriveAddCodegenerator.php
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
class WDMClientUITestDriveAddCodegenerator extends WDMCodegenerator
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
		
		$this->writeStartFile($className."AddView.mxml");

		$this->writeText("<?xml version=\"1.0\" encoding=\"utf-8\"?>		
      <mx:TitleWindow
        xmlns:mx=\"http://www.adobe.com/2006/mxml\"
        layout=\"absolute\" width=\"566\" height=\"440\" title=\"Add New ".$className."\"
        creationComplete=\"onCreationComplete()\"
        close=\"onClose()\"
        showCloseButton=\"true\">
        <mx:Script>
          <![CDATA[
          import ".$this->meta->getClientNamespace().".*;
          import mx.controls.Alert;
          import mx.rpc.events.FaultEvent;
          import mx.core.Application;
          import mx.managers.PopUpManager;
          import mx.rpc.AsyncToken;
          private var _model:".$className." = new ".$className."();

          public static function ShowDialog():void
          {
            PopUpManager.createPopUp( DisplayObject(Application.application),
              ".$className."AddView, true );
          }

          private function onCreationComplete():void
          {
            PopUpManager.centerPopUp(this);\n");


 			foreach ($this->tableMeta->Relations as $relation)
 			{
 				if ($relation->Type == RelationType::Parent)
 				{
 					$relationName = $this->meta->getParentProperty($this->tableName, $relation->RelatedTableName, $relation->ForeignKey, false);
	 				$this->writeText("
			editor".$relationName.".dataProvider =  ActiveRecords.".$this->meta->getClassName($relation->RelatedTableName).".findAll({PageSize:10});\n"); 					
 				}
 			}

			$this->writeText("
	    }

          private function onClose():void
          {
          PopUpManager.removePopUp(this);
          }

          private function onSave():void
          {  

          var asyncToken:AsyncToken = _model.save();

          asyncToken.addResponder(new mx.rpc.Responder(
          function(resultEvent:*):void
          {
          onClose();
          },
          function(faultEvent:FaultEvent):void
          {
          Alert.show(faultEvent.fault.faultString);
          }));
          }

          ]]>
        </mx:Script>");
		
		foreach ($this->tableMeta->Columns as $column)
		{
			if (!$column->isAutoIncrement && !$this->meta->IsBinary($column->dataTypeInfo->dataType))
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
				{
					$source = "";
					switch ($this->meta->getASDataType($column->dataTypeInfo->dataType))
			        { 
			            case "Boolean":
			                $source = "editor".$this->meta->getProperty($this->tableName,$columnName).".selected";
			                break;
			        	case "Date":
			        		$source = "editor".$this->meta->getProperty($this->tableName,$columnName).".selectedDate";
			                break;
			        	case "String":
			        		$source = "editor".$this->meta->getProperty($this->tableName,$columnName).".text";
			                break;			
			            default:
							$source = "{Number(editor".$this->meta->getProperty($this->tableName,$columnName).".text)}";
			        }
					$this->writeText("
          <mx:Binding source=\"".$source."\" destination=\"_model.".$this->meta->getProperty($this->tableName, $columnName)."\" />");					
				}
			}
		}

		foreach ($this->tableMeta->Relations as $relation)
		{
			if ($relation->Type == RelationType::Parent)
			{
				$relationName = $this->meta->getParentProperty($this->tableName, $relation->RelatedTableName, $relation->ForeignKey, false);
				$this->writeText("
          <mx:Binding source=\"".$this->meta->getClassName($relation->RelatedTableName)."(editor".$relationName.".selectedItem)\" destination=\"_model.".$relationName."\" />");
			}
		}

		$this->writeText("
          <mx:VBox width=\"100%\" height=\"100%\">
          <mx:Form width=\"100%\"  height=\"350\">");		
		$relatedFields = array();
		foreach ($this->tableMeta->Relations as $relation)
		{
			if ($relation->Type == RelationType::Parent)
			{
				if (in_array($relationName, $relatedFields))
					continue;
				$relatedFields[] = $relationName;
				$relationName = $this->meta->getParentProperty($this->tableName, $relation->RelatedTableName, $relation->ForeignKey, false);
				$labelField = null;
				foreach ($this->meta->tables[$relation->RelatedTableName]->Columns as $column)
				{
					if ($this->meta->getASDataType($column->dataTypeInfo->dataType) == "String")
					{
						$labelField = $column->name;
						break;
					}
				}
				if (!$labelField)
					$labelField = $this->meta->tables[$relation->RelatedTableName]->Columns[0]->name;

				$this->writeText("
                <mx:FormItem label=\"".$this->meta->getClassName($relation->RelatedTableName)."\" width=\"100%\">
                  <mx:ComboBox id=\"editor".$relationName."\" labelField=\"".$labelField."\" />
                </mx:FormItem>");			
			}
		}

			foreach ($this->tableMeta->Columns as $column)
			{
				if (!$column->isAutoIncrement && !$this->meta->IsBinary($column->dataTypeInfo->dataType))
				{
					$columnName = $column->name;
					$property = $this->meta->getPropertyName($this->tableName, $columnName);
					foreach ($this->tableMeta->Relations as $relation)
					{
						foreach ($relation->Columns as $relationColumns)
						{
							if ($relationColumns->ColumnName == $columnName)
								$columnName = null;
						}
					}
					if ($columnName)
					{	
						$required = "required = \"false\"";
						if ($column->isNullable)
							$required = "required = \"true\"";
						$this->writeText("
                <mx:FormItem label=\"".$property."\" width=\"100%\" ".$required.">");
						switch ($this->meta->getASDataType($column->dataTypeInfo->dataType))
				        { 
				            case "Boolean":
				                $this->writeText("
                      <mx:CheckBox id=\"editor".$property."\" />");
				                break;
				        	case "Date":
				                $this->writeText("
                      <mx:DateField id=\"editor".$property."\" />");
				                break;
				            default:
				                $this->writeText("
                      <mx:TextInput id=\"editor".$property."\" />");
				        }
				        $this->writeText("
                </mx:FormItem>");						
					}
				}
			}

			$this->writeText("
          </mx:Form>
          <mx:ControlBar width=\"100%\" horizontalAlign=\"right\" paddingRight=\"15\">
            <mx:Button label=\"Cancel\" width=\"90\" click=\"onClose()\"/>
            <mx:Button label=\"Save\" width=\"90\" click=\"onSave()\"/>
          </mx:ControlBar>
        </mx:VBox>
      </mx:TitleWindow>");
	
		
		$this->writeEndFile();		
	}
}
?>
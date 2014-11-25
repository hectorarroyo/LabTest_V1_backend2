<?php
/*******************************************************************
 * WDMClientUITestDriveCodegenerator.php
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
class WDMClientUITestDriveCodegenerator extends WDMCodegenerator
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
	
		$this->writeStartFile($className."View.mxml");

		$this->writeText("<?xml version=\"1.0\" encoding=\"utf-8\"?>
      <mx:VBox xmlns:mx=\"http://www.adobe.com/2006/mxml\" width=\"100%\" height=\"100%\" label=\"Products\">
        <mx:Script>
          <![CDATA[
            import mx.controls.Alert;
            import mx.events.CollectionEvent;
            import ".$this->meta->getClientNamespace().".".$className.";
            import ".$this->meta->getClientNamespace().".ActiveRecords;
            import mx.collections.ArrayCollection;

            [Bindable]
            private var _searchResult:ArrayCollection;

            private var _pageSize:int;

            [Bindable]
            public function set pageSize(value:int):void
            {
            _pageSize = value;

            _searchResult = ActiveRecords.".$className.".findAll({PageSize:_pageSize});
            }

            public function get pageSize():int
            {
              return _pageSize;
            }
            
            private function onAddClick():void
            {
            	".$className."AddView.ShowDialog();
            }
          ]]>
          </mx:Script>
        <mx:Button label=\"Add New\" click=\"onAddClick()\" />
        <mx:Label text=\"Table records:\" />
        <mx:DataGrid width=\"100%\" height=\"100%\" dataProvider=\"{_searchResult}\" editable=\"true\">
        <mx:columns>
          <mx:DataGridColumn width=\"70\" editable=\"false\">
            <mx:itemRenderer>
              <mx:Component>
                <mx:HBox horizontalAlign=\"center\">
                    <mx:Button height=\"15\" label=\"save\" enabled=\"{data.IsDirty}\" click=\"{data.save()}\" />
                </mx:HBox>
              </mx:Component>
            </mx:itemRenderer>
          </mx:DataGridColumn>
          <mx:DataGridColumn width=\"80\" editable=\"false\">
            <mx:itemRenderer>
              <mx:Component>
                <mx:HBox horizontalAlign=\"center\">
                    <mx:Button height=\"15\" label=\"remove\" enabled=\"{!data.IsLocked}\" click=\"{data.remove()}\" />
                </mx:HBox>
              </mx:Component>
            </mx:itemRenderer>
          </mx:DataGridColumn>
          ");
		$relatedFields = array();
		foreach ($this->tableMeta->Columns as $column)
		{
			if(in_array($column->name, $relatedFields))
				continue;
			$relatedFields[] = $column->name;
			$editable = "";
			if ($column->keyType == ColumnKeyType::PRIMARY)
				$editable = "editable=\"false\"";
			$this->writeText("
         <mx:DataGridColumn headerText=\"".$this->meta->getProperty($this->tableName, $column->name)."\" dataField=\"".$this->meta->getProperty($this->tableName, $column->name)."\" ".$editable." />");
		}

		$this->writeText("
        </mx:columns>
        </mx:DataGrid>
        <mx:Label text=\"Total records: {_searchResult.length}\" />
      </mx:VBox>");
		$this->writeEndFile();		
	}
}
?>
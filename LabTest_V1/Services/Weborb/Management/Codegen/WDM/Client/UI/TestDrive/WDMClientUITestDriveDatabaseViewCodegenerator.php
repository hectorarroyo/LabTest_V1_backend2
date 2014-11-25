<?php
/*******************************************************************
 * WDMClientUITestDriveDatabaseViewCodegenerator.php
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
class WDMClientUITestDriveDatabaseViewCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		$this->writeStartFile($this->meta->getClassName($this->meta->database->DatabaseName)."DbView.mxml");

		$this->writeText("<?xml version=\"1.0\" encoding=\"utf-8\"?>
	<mx:Canvas xmlns:mx=\"http://www.adobe.com/2006/mxml\" width=\"726\" height=\"650\" xmlns:controls=\"UI.TestDrive.*\" creationComplete=\"onCreationComplete()\">
		<mx:Script>
		<![CDATA[
          [Bindable]
          private var currentRecordName:String = \"\";

          private function changeTable(index:int):void
          {
            vsActiveRecords.selectedIndex = index;
            currentRecordName = vsActiveRecords.selectedChild.label;
            Object(vsActiveRecords.selectedChild).pageSize = Number(cbPageSize.selectedItem);
          }

          private function onCreationComplete():void
          {
            cbPageSize.selectedItem = 40;
            changeTable(0);
          }
          ]]>
		</mx:Script>
        <mx:Binding source=\"cbPageSize.selectedItem\" destination=\"Object(vsActiveRecords.selectedChild).pageSize\" />
        <mx:HBox width=\"100%\" height=\"100%\">
          <mx:VBox height=\"100%\" borderStyle=\"solid\" paddingBottom=\"10\"  paddingTop=\"10\"  shadowDistance=\"3\" cornerRadius=\"10\"  dropShadowEnabled=\"true\" borderThickness=\"2\" backgroundColor=\"#769dbe\" borderColor=\"#294074\" shadowDirection=\"right\" >
            <mx:Label text=\"Active Records\" fontWeight=\"bold\" />
            <mx:Spacer />
            <mx:Canvas bottom=\"10\" top=\"10\" width=\"250\" height=\"100%\"  horizontalScrollPolicy=\"off\">
              <mx:VBox  horizontalScrollPolicy=\"off\" verticalScrollPolicy=\"off\"  height=\"100%\">");
			$count = 0;
			foreach ($this->meta->tables as $tableName=>$tableMeta)
			{
				$this->writeText("
			<mx:LinkButton textAlign=\"left\" width=\"100%\" color=\"#ffffff\" label=\"".$tableName."\" click=\"{changeTable(".$count.")}\" />");
				$count++;
			}
			$this->writeText("
              </mx:VBox>
            </mx:Canvas>
          </mx:VBox>
          <mx:VBox width=\"100%\" height=\"100%\" paddingBottom=\"10\" paddingLeft=\"10\" paddingRight=\"10\" paddingTop=\"10\" borderStyle=\"solid\" borderThickness=\"2\" cornerRadius=\"10\" shadowDirection=\"right\" shadowDistance=\"3\" dropShadowEnabled=\"true\" backgroundColor=\"#769dbe\" borderColor=\"#294074\">
            <mx:HBox>
              <mx:Label text=\"Active Record:\"  fontWeight=\"bold\" fontSize=\"17\"/>
              <mx:Label text=\"{currentRecordName}\" fontWeight=\"bold\" fontSize=\"17\" />
            </mx:HBox>
            <mx:HBox width=\"100%\">
              <mx:Spacer width=\"100%\" />
              <mx:VBox>
                <mx:HBox>
                  <mx:Label text=\"Page size:\" />
                  <mx:ComboBox id=\"cbPageSize\">
                    <mx:dataProvider>
                      <mx:Array>
                        <mx:Number>10</mx:Number>
                        <mx:Number>20</mx:Number>
                        <mx:Number>30</mx:Number>
                        <mx:Number>40</mx:Number>
                        <mx:Number>50</mx:Number>
                      </mx:Array>
                    </mx:dataProvider>
                  </mx:ComboBox>
                </mx:HBox>
              </mx:VBox>
            </mx:HBox>
  
            <mx:ViewStack width=\"100%\" height=\"100%\" id=\"vsActiveRecords\">");
			foreach ($this->meta->tables as $tableName=>$tableMeta)
			{
				$this->writeText("
			<controls:".$this->meta->getClassName($tableName)."View label=\"".$this->meta->getClassName($tableName)."\" />");				
			}
           $this->writeLine("
            </mx:ViewStack>
          </mx:VBox>
        </mx:HBox>
	</mx:Canvas>");

		$this->writeEndFile();		
	}
}
?>
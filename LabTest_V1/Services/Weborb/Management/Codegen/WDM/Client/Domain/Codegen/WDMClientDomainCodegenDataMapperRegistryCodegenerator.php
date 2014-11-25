<?php
/*******************************************************************
 * WDMClientDomainCodegenDataMapperRegistryCodegenerator.php
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
class WDMClientDomainCodegenDataMapperRegistryCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		$this->writeStartFile("_DataMapperRegistry.as");

		$this->writeText("
     package ".$this->meta->getClientNamespace().".Codegen
     {");
		foreach ($this->meta->tables as $nameTable=>$tableMeta)
		{
			$this->writeText("
        import ".$this->meta->getClientNamespace().".".$this->meta->getClassName($nameTable)."DataMapper;");
		}

		$this->writeText("\n
       public class _DataMapperRegistry
       {");

		foreach ($this->meta->tables as $nameTable=>$tableMeta)
		{
			$className = $this->meta->getClassName($nameTable);
			$this->writeText("
          private var m_".$this->meta->getFunctionParameter($nameTable)."DataMapper:".$className."DataMapper;

          public function get ".$className."():".$className."DataMapper
          {
            if(m_".$this->meta->getFunctionParameter($nameTable)."DataMapper == null )
              m_".$this->meta->getFunctionParameter($nameTable)."DataMapper = new ".$className."DataMapper();

            return m_".$this->meta->getFunctionParameter($nameTable)."DataMapper;
          }\n\n");
		}

		$this->writeText("
       }");

		$this->writeText("
     }");

		$this->writeEndFile();

		$this->writeStartFile("_".$this->meta->getClassName($this->meta->database->DatabaseName)."Db.as");

		$this->writeText("
      package ".$this->meta->getClientNamespace().".Codegen
      {
        import mx.rpc.AsyncToken;
        import mx.rpc.remoting.RemoteObject;
        import weborb.data.DataServiceClient;

        public class _".$this->meta->getClassName($this->meta->database->DatabaseName)."Db
        {");

		foreach ($this->meta->storedProcedures as $storedProcedure)
		{
			$this->writeText("
            public function ".$this->meta->getStoredProcedureName($storedProcedure->Name)."(");

			if (count($storedProcedure->Parameters) > 0)
			{
				$count = 0;
				foreach ($storedProcedure->Parameters as $parameter)
				{
					$count++;
					$this->writeText("".$this->meta->getFunctionParameter($parameter->Name).":".$this->meta->getASDataType($parameter->DataType->Name)."");
					if ($count < count($storedProcedure->Parameters))
						$this->writeText(", ");
					else
						$this->writeText("):AsyncToken");
				}
			}
			else
				$this->writeText("):AsyncToken");

			$this->writeText("
            {
              var remoteObject:RemoteObject = DataServiceClient.prepareRemoteObject(\"".$this->meta->getServerNamespace().".".$this->meta->getClassName($this->meta->database->DatabaseName)."Db\");

              return remoteObject.".$this->meta->getStoredProcedureName($storedProcedure->Name)."(");
			if (count($storedProcedure->Parameters) > 0)
			{
				$count = 0;
				foreach ($storedProcedure->Parameters as $parameter)
				{
					$count++;
					$this->writeText("".$this->meta->getFunctionParameter($parameter->Name)."");
					if ($count < count($storedProcedure->Parameters))
						$this->writeText(", ");
					else
						$this->writeText(");");
				}
			}
			else
				$this->writeText(");");

			$this->writeText("
            }\n");
		}

		$this->writeText("
        }
      }");

		$this->writeEndFile();
	}
}
?>
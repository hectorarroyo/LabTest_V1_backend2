<?php
require_once(WebOrb . "../Services/Weborb/Management/ServiceBrowser/ServiceNode.php");
require_once(WebOrb . "Util/Cache/Cache.php");
require_once(WebOrb . "../Services/Weborb/Management/ServiceBrowser/ServiceNamespace.php");
require_once(WebOrb . "../Services/Weborb/Management/ServiceBrowser/ServiceMethod.php");
require_once(WebOrb . "../Services/Weborb/Management/ServiceBrowser/Service.php");

class MonitoredClassRegistry
{
	const dcaURL = "http://localhost/weborb/PHP/Weborb/DCAHandler.php";
	private /*Hashtable*/ $selectedNodes = array();

	function __construct()
	{
		if(Cache::get('selectedNodes', 31104000) != '')
			$this->selectedNodes = Cache::get('selectedNodes', 31104000);
	}
	private function updateDCA()
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::dcaURL);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "action=sendDCA");
		curl_exec($ch);
		curl_close($ch);
	}
	public function addSelectedNode( ServiceNode $node )
	{
		if ($node->Name == "") return;
		if(array_key_exists($node->Name, $this->selectedNodes))
		{
			$tempNode = $this->selectedNodes[$node->Name];
			if ($tempNode->Selected == ServiceNode::FULLY_SELECTED)
					return;
			if ($node->Selected == ServiceNode::FULLY_SELECTED)
			{
				$this->selectedNodes[$node->Name] = $node;
				Cache::put('selectedNodes', $this->selectedNodes);
				$this->updateDCA();
				return;
			}
			$this->addNode($tempNode, $node);
		}
		else
		{
			$this->selectedNodes[$node->Name] = $node;
		}
		Cache::put('selectedNodes', $this->selectedNodes);
		$this->updateDCA();
	}

	private function addNode(ServiceNode $selectedNode, ServiceNode $node)
	{
		if ($node->Selected == ServiceNode::FULLY_SELECTED)
		{
			$selectedNode = $node;
			return;
		}
		if ($selectedNode->Selected == ServiceNode::FULLY_SELECTED)
		{
			return;
		}

		foreach ($node->Items as $Item)
		{
			if ($selectedNode->findItem($Item->Name) == null)
				$selectedNode->Items[] = $Item;
			else
				$this->addNode($selectedNode->findItem($Item->Name), $Item);
		}
	}


	public function removeSelectedNode( ServiceNode $node, /*String*/ $fullName )
	{
			/*String[]*/ $nameParts = explode('.', $fullName);
			/*int*/ $i = 0;
			/*ServiceNode*/ $tempNode = null;

			while( true )
			{
				/*String*/ $name = $nameParts[ $i ];
				$i++;
				/*ServiceNode*/ $selectedNode = $tempNode == null
					? $this->selectedNodes[$name]
					: $tempNode->findItem( $name );

				if( $selectedNode->Selected != $node->Selected )
				{
					if( $selectedNode->Parent == null )
                    {
						if( $node->Selected == ServiceNode::NOT_SELECTED )
                        {
							unset($this->selectedNodes[$name]);
                        }
						else
						{
							$this->selectedNodes[$name] = $node;

							while( $i < count($nameParts) )
							{
								$node = $node->findItem( $nameParts[ $i ] );
								$i++;
							}

							$node->Parent->removeItem( $node );
						}
                    }
					elseif( $node->Selected == ServiceNode::NOT_SELECTED )
                    {
                        $selectedItem = $this->findInSelected($selectedNode->Parent->getFullName());
						$selectedItem->removeItem($selectedItem->findItem($name));
                    }
					else
					{
                        $parentSelectedNode = $selectedNode->Parent;
						$parentSelectedNode->removeItem($parentSelectedNode->findItem( $name ) );


						while( $i < count($nameParts) )
						{
							$nodeToRemove = $node->findItem( $nameParts[ $i ] );
							$i++;
						}
                           $node->removeItem( $nodeToRemove );
                           $parentSelectedNode->Items[] = $node;
					}

					break;
				}
				else
				{
					$tempNode = $selectedNode;
					$node = $node->findItem( $nameParts[ $i ] );
				}
			}
			Cache::put('selectedNodes', $this->selectedNodes);
			$this->updateDCA();
	}
    public function findInSelected($fullName)
    {
        $nameParts = explode('.', $fullName);
        $tempNode = null;
        for($i= 0; $i<count($nameParts); $i++)
        {
            $tempNode = $tempNode == null
                    ? $this->selectedNodes[$nameParts[$i]]
                    : $tempNode->findItem( $nameParts[$i] );
        }
        return $tempNode;
    }
	public /*int*/function isSelected( /*String*/ $parentName )
	{
		/*int*/ $selected = ServiceNode::PARTLY_SELECTED;
//		/*String */$arguments = "";

//		if( strpos($parentName, "(" ) !== false )
//		{
//			$arguments = substr($parentName, 0, strpos( $parentName, "(" ) );
//			$parentName = substr($parentName, strpos( $parentName, "(" ), strlen($parentName)-1);
//			$parentName = substr($parentName, 0, strpos( $parentName, "(" ) );
//		}

		/*String[]*/ $nameParts = explode('.', $parentName);
//		$nameParts[ count($nameParts) - 1 ] .= $arguments;
		/*ServiceNode*/ $node = null;

		for( $i = 0; $i < count($nameParts); $i++ )
		{
			if( $i == 0 )
			{
				if( array_key_exists( $nameParts[ $i ], $this->selectedNodes ) )
					$node = $this->selectedNodes[$nameParts[ $i ]];
				else
					$node = null;
			}
			else
			{
				$node = $node->findItem( $nameParts[ $i ] );
			}

			if( $node == null )
				return ServiceNode::NOT_SELECTED;

			if( $node->Selected == ServiceNode::FULLY_SELECTED )
				return ServiceNode::FULLY_SELECTED;
		}

		$selected = $node != null ? $node->Selected : ServiceNode::NOT_SELECTED;

		return $selected;
	}

	public /*ArrayList*/function getSelectedNodes()
	{
		/*ArrayList*/ $list = array();
		foreach ($this->selectedNodes as $node)
			$list[] = $node;

		return $list;
	}

	public function clear()
	{
		$this->selectedNodes = array();
		Cache::put('selectedNodes', $this->selectedNodes);
	}
}
?>
<?php
require_once("DomainObject.php");

class IdentityMap
{
    public /*List<String>*/ $items = array();
    
    public /*void*/ function register( /*DomainObject*/ $domainObject )
    {
        /*string*/ $uri = $domainObject->getUri();

        if( !array_key_exists( $uri, $this->items ) )
            $this->items[] = $uri;        
    }

    public /*bool*/ function contains( /*DomainObject*/ $domainObject )
    {
        return array_key_exists( $domainObject->getUri(), $this->items );
    }

    public /*void*/ function remove( /*DomainObject*/ $domainObject )
    {
        unset( $this->items[ $domainObject->getUri() ] );
    }
}

?>
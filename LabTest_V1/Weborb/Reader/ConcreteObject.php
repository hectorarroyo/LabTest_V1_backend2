<?php

class ConcreteObject implements IAdaptingType
{
	private /*Object*/ $obj;

    public function __construct( /*Object*/ $obj )
    {
        $this->obj = $obj;
    }

    public /*Class*/function getDefaultType()
    {
        return $this->obj->getClass();
    }

    public /*Object*/function defaultAdapt()
    {
        return $this->obj;
    }

    public /*Object*/function adapt( /*Class*/ $type )
    {
        return $this->obj;
    }

    public /*boolean*/function canAdaptTo(/*Class*/ $formalArg )
    {
        return $this->obj->getClass()->isAssignableFrom( $formalArg );
    }

    public /*String*/function ToString()
    {
        return "Concrete object - " . $this->obj;
    }
}
?>
<?php
require_once("SqlCommandOptions.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

class DynamicMethod
{
    public static /*List<String>*/ $Tokens = array( "and", "or", "find", "findby", "create", "by", "init", "initialize" );
    /*SqlCommandOptions*/public $CommandOptions;

    public function __construct( /*String*/ $method, /*ArrayList*/ $args)
    {
    	$this->CommandOptions = new SqlCommandOptions();
        $this->analyze( $method, $args);
    }

    private /*void*/ function analyze( /*String*/ $method, /*ArrayList*/ $args)
    {
        /*Queue<String>*/ $tokens = $this->getTokens( $method );
        error_log( "TOKENS ". print_r( $tokens, true ), 0 );
    	/*String*/ $conditions = "";
		/*int*/ $argumentIndex = 0;
		for( /*int*/ $i = 0; $i < count( $tokens ); $i++ )
        {
        	/*String*/ $token = $tokens[ $i ];

            switch( $token )
            {
                case "initialize":
                case "init":
                    $this->CommandOptions->InitializeIfNotFound = true;
                    $i++; // skip next token
                    break;
                case "find":
                    $i++; // skip next token
                    break;
                case "findBy":
                    break;
                case "create":
                    $this->CommandOptions->CreateIfNotFound = true;
                    $i++; // skip next token
                    break;
                case "and":
                    $conditions .= " and ";
                    break;
                case "or":
                    $conditions .= " or ";
                    break;
                default:
                    $conditions .=  $token . " = :" . $token;
                    $this->CommandOptions->Fields[ $token ] = $args[ $argumentIndex++ ];
                    break;
            }
        }

        $this->CommandOptions->Conditions = $conditions;
    }


    private /*Queue<String>*/ function getTokens( /*String*/ $dynamicMethod )
    {
        /*Queue<String>*/ $tokens = array();
        /*int*/ $charIndex = 0;
        /*String*/ $buffer = "";
        /*Queue<String>*/ $unknownTokens = array();

        while( true )
        {
            /*char*/ $symbol = $dynamicMethod{ $charIndex };
            /*bool*/ $lastChar = $charIndex == strlen( $dynamicMethod ) - 1;

            $tokenCheck = in_array( strtolower( $buffer ), self::$Tokens );

            if( $tokenCheck || (strtoupper( $symbol ) == $symbol && strlen( $buffer ) > 0 || $lastChar) )
            {
                if( $lastChar )
                    $buffer .= $symbol;

				error_log( "CHECKING BUFFER " . $buffer );
                /*bool*/ $isKnownToken = in_array( strtolower( $buffer ), self::$Tokens );

                if( $isKnownToken || $lastChar )
                {
                    if( count( $unknownTokens ) > 0)
                    {
                        /*String*/ $previousToken = "";

                        while( count( $unknownTokens ) > 0)
//						foreach( $unknownTokens as /*String*/ $unknownToken )
						{
							$unknownTokens = array_reverse($unknownTokens);
                            $previousToken .= array_pop($unknownTokens);
                            $unknownTokens = array_reverse($unknownTokens);

						}

                        if( $isKnownToken )
                            $tokens[] = $previousToken;
                        else
                        {
                            $previousToken .= $buffer;
                            $buffer = $previousToken;
                        }
                    }

                    if( $isKnownToken )
                        $tokens[] = strtolower( $buffer );
                    else
                        $tokens[] = $buffer;
                }
                else
                    $unknownTokens[] = $buffer;

                $buffer = "";
            }

            if ($lastChar)
                break;

            $buffer .= $symbol;
            $charIndex++;
        }

        return $tokens;
    }
}

?>
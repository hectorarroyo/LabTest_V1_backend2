<?php
/*******************************************************************
 * FlashorbBinaryReader.php
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



require_once(WebOrb . "Util/Logging/Log.php");

class FlashorbBinaryReader
{

    private $m_stream;
    private $m_currentByte;

    public function __construct($stream)
    {
        $this->m_stream = $stream;
        $this->m_currentByte = 0;
    }

    public function readByte()
    {
        $returnValue = ord($this->m_stream[$this->m_currentByte++]);

        return $returnValue;
    }

    public function readUnsignedShort()
    {

        $firstByte = $this->ReadByte();
        $secondByte = $this->ReadByte();

        return (($firstByte << 8) | $secondByte);
    }

    public function readBytes($length)
    {
        $buffer = substr($this->m_stream, $this->m_currentByte, $length);

        $this->m_currentByte += $length;

        return $buffer;
    }

    public function skip($length)
    {
      $this->m_currentByte += $length;
    }

    public function readUTF($len = null)
    {
        $utfString = "";
        
        if(is_null($len))
        {
            $buffer = $this->readBytes($this->readUnsignedShort());

            $utfString = $buffer;//UTF8Encoding::getString($buffer);
        }
        else
        {
            $buffer = $this->readBytes($len);
        
            /*$i = 0;
            
            while($i < $len)
            {
              $c = $buffer[$i] & 0xFF;
              $code = $c >> 4;
              
              if($code >= 0 && $code <= 7)
              {
                $i++;
                $utfString .= $c;
              }
              else if($code == 12 || $code == 13)
              {
                $i += 2;
                
                if($i > $len)
                    throw new Exception("Invalid UTF data");
                
                $char2 = $buffer[$i - 1];
                
                if(($char2 & 0xC0) != 128)
                    throw new Exception("Invalid UTF data 2");
                    
                $utfString .= (($c & 0x1F) << 6 | $char2 & 0x3F);    
              }
              else if($code == 14)
              {
                $i += 3;
                
                if($i > $len)  
                    throw new Exception("Invalid UTF data 3");
                    
                $char2 = $buffer[$i - 2];
                $char2 = $buffer[$i - 1];
                
                if((($char2 & 0xC0) != 128) && (($char3 & 0xC0) != 128))
                    throw new Exception("Invalid UTF data 4");
                    
                $utfString .= (($c & 0x1F) << 12 | ($char2 & 0x3F) << 6 | ($char3 & 0x3F) << 0);       
                
              }
              else
              {
                throw new Exception("Invalid UTF data 5");
              }
              
              
            }*/
            $utfString = $buffer;
         }

        return $utfString;
    }

    public function readBoolean()
    {
        return $this->readByte() != 0;
    }

    public function readInteger()
    {
        $byte1 = $this->readByte();
        $byte2 = $this->readByte();
        $byte3 = $this->readByte();
        $byte4 = $this->readByte();

        return (((($byte1 << 0x18) + ($byte2 << 0x10)) + ($byte3 << 8)) + $byte4);
    }

    public function readDouble()
    {
        $bytes = substr($this->m_stream, $this->m_currentByte, 8);
        
        $this->m_currentByte += 8;
        
        $dblBuff = unpack("dflt", strrev($bytes));

        return  $dblBuff['flt'];
    }

    public function readVarInteger()
    {
        $byte = $this->readByte();
      
        $num = $byte & 0xFF;
     
        if ($num < 128)
            return $num; 
        
        $val = ($num & 0x7F) << 7;
        $num = $this->readByte() & 0xFF;

        if ($num < 128)
            return ($val | $num);

        $val = ($val | $num & 0x7F) << 7;
        $num = $this->readByte() & 0xFF;

        if ($num < 128)
            return ($val | $num);

        $val = ($val | $num & 0x7F) << 8;
        $num = $this->readByte() & 0xFF;       
        
        return  ($val | $num);
    }

}

?>

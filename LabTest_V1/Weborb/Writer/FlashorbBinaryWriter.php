<?php
/*******************************************************************
 * FlashorbBinaryWriter.php
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

class FlashorbBinaryWriter
{

    private $m_stream;

    public function __construct(&$stream)
    {
       $this->m_stream = &$stream;
    }

    public function write($value)
    {
        //$this->m_stream .= chr($value & 0xff);
        $this->m_stream .= pack("c", $value);
    }

    public function writeDouble($value)
    {
        $double = pack("d", $value);

        $this->m_stream .= strrev($double);
    }

    public function writeInt($value)
    {
        $this->m_stream .= pack("N", $value);
    }

    public function writeShort($value)
    {
        $this->m_stream .= pack("n", $value);
    }

    public function writeUtf(&$utfValue, $long = FALSE)
    {
        //$utfValue = $value;//utf8_encode($value);
        $utfValue = utf8_encode($utfValue);

        $strlen = strlen($utfValue);

        if ($strlen <= 65535)
        {
            $utfLen = $strlen;
        }
        else
        {
            $utfLen = $strlen << 1 | 0x1;
        }

        if (!$long && $utfLen > 65535)
        {
            throw new ApplicationException( "utf data format exception" );
        }

        if ($long)
        {
            $this->writeVarInt($strlen << 1 | 0x1);
        }
        else
        {
            $this->writeShort($strlen);
        }


        $this->m_stream .= $utfValue;
    }

    public function writeVarInt($v)
    {
        if ($v < 128)
        {
            //$this->write($v);
              $this->m_stream .= pack("c", $v);
        }
        else if ($v < 16384)
        {
            //$this->write($v >> 7 & 0x7F | 0x80);
            //$this->write($v & 0x7F);

            $this->m_stream .= chr($v >> 7 & 0x7F | 0x80 ) . chr($v & 0x7F);
        }
        else if ($v < 2097152)
        {
            //$this->write($v >> 14 & 0x7F | 0x80);
            //$this->write($v >> 7 & 0x7F | 0x80);
            //$this->write($v & 0x7F);

            $this->m_stream .= chr($v >> 14 & 0x7F | 0x80) . chr($v >> 7 & 0x7F | 0x80) . chr($v & 0x7F);
        }
        else if ($v < 1073741824)
        {
            //$this->write($v >> 22 & 0x7F | 0x80);
            //$this->write($v >> 15 & 0x7F | 0x80);
            //$this->write($v >> 8 & 0x7F | 0x80);
            //$this->write($v & 0xFF);

            $this->m_stream .= chr($v >> 22 & 0x7F | 0x80) . chr($v >> 15 & 0x7F | 0x80) . chr($v >> 8 & 0x7F | 0x80) . chr($v & 0xFF);
        }
        else
        {
            throw new ServiceException("value out of range - " + $v);
        }
    }

    public function writeLong($value)
    {
		$this->m_stream .= pack('l',$value);

    }
    public function getStream()
    {
        return $this->m_stream;
    }

}

?>

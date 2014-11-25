<?php
/*******************************************************************
 * PrimitiveTest.php
 * Copyright (C) 2006-2007 Midnight Coders, LLC
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * The software is licensed under the GNU General Public License (GPL)
 * For details, see http://www.gnu.org/licenses/gpl.txt.
 ********************************************************************/



require_once(WebOrb . "Util/Logging/Log.php");

class PrimitiveTest
{

    public function echoInt($i)
    {
        return $i;
    }

    public function echoLong($l)
    {
        return $l;
    }

    public function echoShort($s)
    {
        return $s;
    }

    public function echoByte($b)
    {
        return $b;
    }

    public function echoFloat($f)
    {
        return $f;
    }

    public function echoDouble($d)
    {
        return $d;
    }

    public function echoChar($c)
    {
        return $c;
    }

    public function echoBoolean($b)
    {
        return $b;
    }

    public function echoDate($d)
    {
        return $d;
    }

    public function echoNull($n)
    {
        return $n;
    }
    /*
    public function getDateTime(DateTime $dateTime)
    {
        return $dateTime;
    }
    */
    public function getDateTime($dateTime)
    {
    	return $dateTime;
    }
}

?>

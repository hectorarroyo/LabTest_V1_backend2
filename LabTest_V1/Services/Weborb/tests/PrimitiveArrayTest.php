<?php
/*******************************************************************
 * PrimitiveArrayTest.php
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



class PrimitiveArrayTest
{

    public function echoIntArray($i)
    {
        return $i;
    }

    public function echoFloatArray($f)
    {
        return $f;
    }

    public function echoBooleanArray($b)
    {
        return $b;
    }

    public function echoEmptyArray($a)
    {
        return $a;
    }

    public function echoNullArray($a)
    {
        return $a;
    }

    public function echoLongArray($l)
    {
        return $l;
    }

    public function echoShortArray($s)
    {
        return $s;
    }

    public function echoDoubleArray($d)
    {
        return $d;
    }

    public function echoByteArray($b)
    {
        return $b;
    }

    public function getArrayObject($array)
    {
        return new ArrayObject($array);
    }
}

?>

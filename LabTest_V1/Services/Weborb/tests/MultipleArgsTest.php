<?php
/*******************************************************************
 * MultipleArgsTest.php
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



require_once("ComplexType.php");

class MultipleArgsTest
{

    public function echoInts($i1, $i2, $i3, $i4)
    {
        return array($i1, $i2, $i3, $i4);
    }

    public function echoIntString($i, $s)
    {
        return array($i, $s);
    }

    public function echoIntNullString($i, $s)
    {
        return array($i, $s);
    }

    public function echoLotsOfArgs($v, $a, $h, $ct, $i, $ca, $s, $dateObj, $str)
    {
        return array($v, $a, $h, $ct, $i, $ca, $s, $dateObj, $str);
    }

    public function echoShorts($s1, $s2, $s3)
    {
        return array($s1, $s2, $s3);
    }

    public function echoIntLongs($i, $l)
    {
        return array($i, $l);
    }

    public function echoCharString($c, $s)
    {
        return array($c, $s);
    }

    public function echoStringBuilderDouble($sb, $d)
    {
        return array($sb, $d);
    }

    public function echoNullStringBuilderDouble($sb, $d)
    {
        return array($sb, $d);
    }

}

?>

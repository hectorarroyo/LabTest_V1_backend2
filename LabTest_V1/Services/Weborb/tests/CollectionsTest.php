<?php
/*******************************************************************
 * CollectionsTest.php
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



require_once( "ComplexType.php");

class CollectionsTest
{

    public function echoArray($i)
    {
        return $i;
    }

    public function echoComplexArray($array)
    {
        return $array;
    }

    public function echoQueue($q)
    {
        return $q;
    }

    public function echoStack($s)
    {
        return $s;
    }

    public function echoSortedList($s)
    {
        return $s;
    }

    public function echoArrayList($list)
    {
        return $list;
    }

    public function echoHybridDictionary($d)
    {
        return $d;
    }

    public function echoListDictionary($props)
    {
        return $props;
    }

    public function echoHashtable($map)
    {
        return $map;
    }

    public function echoPropertyCollection($map)
    {
        return $map;
    }

    public function echoStringDictionary($sb)
    {
        return $sb;
    }

    public function echoStringCollection($s)
    {
        return $s;
    }

    public function echoNameValueCollection($map)
    {
        return $map;
    }

}

?>

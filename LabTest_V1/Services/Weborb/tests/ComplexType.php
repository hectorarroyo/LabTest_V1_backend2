<?php
/*******************************************************************
 * ComplexType.php
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



class ComplexType
{

    public $number;
    public $date;
    public $nullableDate;
    public $str;
    public $array;
    public $complexArray;
    public $map;
    public $complexType;

    // primitive fields
    public $intField;
    public $floatField;
    public $longField;
    public $shortField;
    //public $charField;
    public $byteField;
    public $doubleField;

    // primitive wrappers fields
    public $intWrapperField;
    public $floatWrapperField;
    public $longWrapperField;
    public $shortWrapperField;
    //  public $charWrapperField;
    public $byteWrapperField;
    public $doubleWrapperField;

    // string
    public $stringField;
    public $stringBufferField;

    // arrays of primitives
    public $intArrayField;
    public $shortArrayField;
    public $longArrayField;
    // public $charArrayField;
    public $byteArrayField;
    public $doubleArrayField;
    public $floatArrayField;

    // arrays of wrappers
    public $intWrapperArrayField;
    public $shortWrapperArrayField;
    public $longWrapperArrayField;
    // public $charWrapperArrayField;
    public $byteWrapperArrayField;
    public $doubleWrapperArrayField;
    public $floatWrapperArrayField;

    // collections
    public $collectionField;
    public $abstractCollectionField;
    public $listCollectionField;
    public $abstractListField;
    public $abstractSequentialListField;
    public $stackField;
    public $vectorField;
    public $linkedLIstField;
    public $arrayListField;
    public $setField;
    public $sortedSetField;
    public $abstractSetField;
    // public $hashSetField;
    public $treeSetField;
    public $mapSetField;
    public $abstractMapField;
    public $propertiesField;
    // public $hashtableField;
    // public $hashMapField;
    // public $weakHashMapField;
    public $treeMapField;

}

?>

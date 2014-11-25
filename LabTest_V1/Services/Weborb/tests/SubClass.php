<?php
/*******************************************************************
 * SubClass.php
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



require_once("BaseClass.php");

class SubClass
    extends BaseClass
{

    public $field1;
    public $field2;

    public function __construct()
    {
        $this->field1 = "field 1";
        $this->field2 = "field 2";
    }

    public function getField1()
    {
        return $this->field1;
    }

    public function setField1($value)
    {
        $this->field1 = $value;
    }

    public function getField2()
    {
        return $this->field2;
    }

    public function setField2($value)
    {
        $this->field2 = $value;
    }

}

?>

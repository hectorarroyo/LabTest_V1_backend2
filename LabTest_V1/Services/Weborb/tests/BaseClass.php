<?php
/*******************************************************************
 * BaseClass.php
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



class BaseClass
{

    public $baseField1;
    public $baseField2;

    public function __construct()
    {
        $this->baseField1 = "base field 1";
        $this->baseField2 = "base field 2";
    }

    public function getBaseField1()
    {
        return $this->baseField1;
    }

    public function setBaseField1($value)
    {
        $this->baseField1 = $value;
    }

    public function getBaseField2()
    {
        return $this->baseField2;
    }

    public function setBaseField2($value)
    {
        $this->baseField2 = $value;
    }

}

?>

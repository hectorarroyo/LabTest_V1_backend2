<?php
/*******************************************************************
 * Employee.php
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



class Employee
{

    public $employeeId;
    public $employeeName;
    public $employeeTitle;
    public $phoneNumber;

    public function getEmployeeName()
    {
        return $this->employeeName;
    }

    public function setEmployeeName($value)
    {
        $this->employeeName = $value;
    }

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setEmployeeId($value)
    {
        $this->employeeId = $value;
    }

    public function getEmployeeTitle()
    {
        return $this->employeeTitle;
    }

    public function setEmployeeTitle($value)
    {
        $this->employeeTitle = $value;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($value)
    {
        $this->phoneNumber = $value;
    }

}

?>

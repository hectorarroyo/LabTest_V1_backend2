<?php
/*******************************************************************
 * ComplexTypesTest.php
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



require_once( WebOrbServicesPath . "Weborb/tests/ComplexType.php");
require_once( WebOrbServicesPath . "Weborb/tests/SubClass.php");
require_once( WebOrbServicesPath . "Weborb/tests/Employee.php");
require_once( WebOrbServicesPath . "Weborb/tests/ListMember.php");
require_once( WebOrb . "Util/Logging/Log.php");

class ComplexTypesTest
{

    public function echoComplexType(ComplexType $c)
    {
        Log::log(LoggingConstants::DEBUG, var_export($c, TRUE));

        return $c;
    }

    public function echoNullComplexType($c)
    {
        return $c;
    }

    public function echoSubclass(SubClass $subClass)
    {
        return $subClass;
    }

    public function getEmployee($name)
    {
        $emp = new Employee();
        $emp->setEmployeeName($name);
        return $emp;
    }

    public function setEmployee(Employee $emp)
    {
        Log::log(LoggingConstants::DEBUG, var_export($emp, TRUE));
        Log::log(LoggingConstants::DEBUG, count($emp));
        Log::log(LoggingConstants::DEBUG, $emp->getEmployeeName());
        $emp->setEmployeeName("Joe Orbman");
        return $emp;
    }

    public function echoEmployee($emp)
    {
        return $emp;
    }

    public function getCrossReferencedObject($length)
    {
        $list = array();

        for ($index = 0; $index < $length; $index ++)
        {
            $list[] = new ListMember($list);
        }

        return $list;
    }

}

?>

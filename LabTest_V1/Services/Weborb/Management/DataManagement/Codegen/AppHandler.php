<?php
/*******************************************************************
 * AppHandler.php
 * Copyright (C) 2007 Midnight Coders, LLC
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


require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");
require_once(WebOrbServicesPath. "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "Codegen" . DIRECTORY_SEPARATOR . "WDM" . DIRECTORY_SEPARATOR . "WDMCodegenerator.php");
require_once(WebOrbServicesPath. "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "Codegen" . DIRECTORY_SEPARATOR . "WDM" . DIRECTORY_SEPARATOR . "MetaInspector" . DIRECTORY_SEPARATOR . "WDMMetaInspectorFactory.php");
require_once(WebOrbServicesPath. "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "DataManagement" . DIRECTORY_SEPARATOR . "DataManagementService.php");
require_once(WebOrbServicesPath. "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "Codegen" . DIRECTORY_SEPARATOR . "NameMapper.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "ZIP" . DIRECTORY_SEPARATOR . "CreateArc.php");
class AppHandler
{
  public /*int*/function generate( /*String*/ $modelName )
  {
  /*
    $userSettings = DataManagementService::GetSettings();
    $userDataModel = $userSettings->GetModel( $modelName );
    $codegenResult = null;

    $codegen = new WDMCodegenerator();
    $nameMapper = new NameMapper( $userSettings, $userDataModel );

	$serverType = $userDataModel->getServerConnection()->Connection->Type;
	$metaData = WDMMetaInspectorFactory::getInspector($serverType);
	$metaData->initialize($userDataModel, $nameMapper);
	$codegen->setMeta($metaData);

	$codegenResult = $codegen->Generate();
	$userDataModel->LOC = $codegenResult->LineCount;
	CreateArc::createArchive($codegenResult, $modelName . ".zip", "weborbassets" . DIRECTORY_SEPARATOR . "wdm" . DIRECTORY_SEPARATOR . "output", "weborbassets" . DIRECTORY_SEPARATOR . "wdm" );
	return $userDataModel->LOC;
	*/
	$wdmService = new DataManagementService();
	return $wdmService->Generate( $modelName );
  }

}
?>
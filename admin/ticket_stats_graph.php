<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/prolog.php");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

require_once('classes/Facade.php');

global $USER, $APPLICATION;

use admin\classes\Facade;
use admin\classes\SubsystemCAdmin;
use admin\classes\SubsystemFilterForm;
use admin\classes\SubsystemGraph;
use admin\classes\SubsystemRole;
use admin\classes\SubsystemSupportUser;
use admin\classes\SubsystemTicket;

$subsystemRole = new SubsystemRole();
$subsystemGraph = new SubsystemGraph();
$subsystemCAdmin = new SubsystemCAdmin();
$subsystemTicket = new SubsystemTicket();
$subsystemSupportUser = new SubsystemSupportUser();
$subsystemFilterForm = new SubsystemFilterForm();

$facade = new Facade(
    $subsystemRole,
    $subsystemGraph,
    $subsystemCAdmin,
    $subsystemTicket,
    $subsystemSupportUser,
    $subsystemFilterForm
);

list('bAdmin' => $bAdmin, 'bDemo' => $bDemo) =  $facade -> getSubsystemRole() -> showAuthFormByRole(true);

$sTableID = $facade -> getSubsystemGraph() -> getGraph() -> addProperty("sTableID", 't_report_graph', true);

$defaultFilterValues = $facade -> getSubsystemCAdmin() -> initCAdminPropertyList($sTableID, true);
//проверил до точки
if ($facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> IsDefaultFilter()) $facade -> getSubsystemCAdmin() -> getAdmin() -> addValDefaultFilter();
$arFilterFields = $facade -> getSubsystemCAdmin() -> getAdmin() -> addArFilterFields(true);

$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> InitFilter($arFilterFields);
//проверил до точки
if ($bAdmin != "Y" && $bDemo != "Y") $find_responsible_id = $USER -> GetID();
InitBVar($find_responsible_exact_match);

$findData = [
    "find_site" => $find_site,
    "find_date1" => $find_date1,
    "find_date2" => $find_date2,
    "find_responsible_id" => $find_responsible_id,
    "find_responsible" => $find_responsible,
    "find_responsible_exact_match" => $find_responsible_exact_match,
    "find_sla_id" => $find_sla_id,
    "find_category_id" => $finds_category_id,
    "find_criticality_id" => $find_criticality_id,
    "find_status_id" => $find_status_id,
    "find_mark_id" => $find_mark_id,
    "find_source_id" => $find_source_id
];

$facade -> getSubsystemCAdmin() -> getAdmin() -> addArFilterData($findData);

$arUsersID = $facade -> getSubsystemTicket() -> initTicketProperty($facade -> getSubsystemCAdmin() -> getAdmin());

$facade -> getSubsystemSupportUser() -> addUsers($arUsersID);

//ob_start
$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> BeginCustomContent();
$facade -> getSubsystemCAdmin() -> showErrorMessageIfExist(); ?>

    <!--HTML CONTENT-->

    <p><? echo GetMessage("SUP_SERVER_TIME") . "&nbsp;" . GetTime(time(), "FULL") ?></p>
    <h2><?= GetMessage("SUP_GRAPH_ALT") ?></h2>

    <!--HTML CONTENT-->

<?php

//Image
$facade -> getSubsystemGraph() -> createImage(
    $facade->getSubsystemTicket()->getTicket(),
    $facade -> getSubsystemCAdmin() -> getAdmin(),
    $arrColor,
    "576",
    "400"
);

$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty("lAdmin") -> EndCustomContent();
$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty("lAdmin") -> CheckListMode();

global $APPLICATION;
$APPLICATION -> SetTitle(GetMessage("SUP_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

//form
$arFilterFormProps = [
    "filter" => $facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('filter'),
    "bAdmin" => $bAdmin,
    "bDemo" => $bDemo,
    "arrSupportUser" => $facade -> getSubsystemSupportUser() -> getArrSupportUser(),
    "sTableID" => $sTableID
];

$arFilterProps = [
    "find_open" => $find_open,
    "find_close" => $find_close,
    "find_all" => $find_all,
    "find_mess" => $find_mess,
    "find_overdue_mess" => $find_overdue_mess,
];

$arFilterFormProps = array_merge_recursive($arFilterFormProps, $findData, $defaultFilterValues ?? $arFilterProps);

$facade -> getSubsystemFilterForm() -> initFilterFormProperty($arFilterFormProps);
$facade -> getSubsystemFilterForm() -> createAndShowFilterForm();

//ob_get_contents
$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

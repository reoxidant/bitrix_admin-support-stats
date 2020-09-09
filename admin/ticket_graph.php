<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/prolog.php"); // пролог модуля

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php"); // инициализация модуля

// подключим языковой файл
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/colors.php"); // подключим цвета для графика
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/muiv.support/admin/classes/Facade.php"); //смотри паттерн фасад

global $USER, $APPLICATION;

use admin\classes\Facade;
use admin\classes\SubsystemCAdmin;
use admin\classes\SubsystemFilterForm;
use admin\classes\SubsystemGraph;
use admin\classes\SubsystemRole;
use admin\classes\SubsystemTicket;

$subsystemRole = new SubsystemRole();
$subsystemGraph = new SubsystemGraph();
$subsystemCAdmin = new SubsystemCAdmin();
$subsystemTicket = new SubsystemTicket();
$subsystemFilterForm = new SubsystemFilterForm();

$facade = new Facade(
    $subsystemRole,
    $subsystemGraph,
    $subsystemCAdmin,
    $subsystemTicket,
    $subsystemFilterForm
);

list('bAdmin' => $bAdmin, 'bDemo' => $bDemo) = $facade -> getSubsystemRole() -> showAuthFormByRole(true);

//all ok

$sTableID = $facade -> getSubsystemGraph() -> getGraph() -> addProperty("sTableID", 't_report_graph', true);

$facade -> getSubsystemCAdmin() -> initCAdminPropertyList($sTableID);

if ($facade -> getSubsystemCAdmin() -> getAdmin() -> IsDefaultFilter())
    $defaultDate = date('d.m.Y', strtotime("-30 day"));

$arFilterFields = $facade -> getSubsystemCAdmin() -> getAdmin() -> addArFilterFields(true);

if (empty($defaultFilterValues)) {
    foreach ($_SESSION["SESS_MUIV"][$sTableID] as $key => $val) {
        global $$key;
        if (isset($$key)) $$key = $val;
    }
}

if ($bAdmin != "Y" && $bDemo != "Y") $find_responsible_id = $USER -> GetID();

InitBVar($find_responsible_exact_match);

//TODO : need to understand why i don't have date interval when i got when

$arFilterProps = [
    "find_site" => $find_site,
    "find_date1" => $defaultDate ?? $find_date1,
    "find_date2" => $find_date2,
    "find_responsible_id" => $find_responsible_id,
    "find_responsible" => $find_responsible,
    "find_responsible_exact_match" => $find_responsible_exact_match,
    "find_sla_id" => $find_sla_id,
    "find_category_id" => 20,
    "find_criticality_id" => $find_criticality_id,
    "find_status_id" => $find_status_id,
    "find_mark_id" => $find_mark_id,
    "find_source_id" => $find_source_id,
    "find_mess" => $find_mess ?? "Y",
    "find_overdue_mess" => $find_overdue_mess ?? "Y",
];

$facade -> getSubsystemCAdmin() -> getAdmin() -> initSessionFilter($arFilterFields);

$facade -> getSubsystemCAdmin() -> getAdmin() -> addArFilterData($arFilterProps);

$arUsersID = $facade -> getSubsystemTicket() -> initTicketProperty($facade -> getSubsystemCAdmin() -> getAdmin());

//ob_start
$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> BeginCustomContent();
$facade -> getSubsystemCAdmin() -> showErrorMessageIfExist(); ?>

    <!--HTML CONTENT-->

    <p><? echo GetMessage("SUP_SERVER_TIME") . "&nbsp;" . GetTime(time(), "FULL") ?></p>
    <h2><?= GetMessage("SUP_GRAPH_ALT") ?></h2>

    <!--HTML CONTENT-->

<?php

//Image
try {
    $facade -> getSubsystemGraph() -> createImage(
        $facade -> getSubsystemTicket() -> getTicket(),
        $facade -> getSubsystemCAdmin() -> getAdmin(),
        $arrColor,
        $defaultFilterValues ?? $arFilterProps,
        "576",
        "400"
    );
} catch (\Exception $e) {
    $e -> getMessage();
}

$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty("lAdmin") -> EndCustomContent();

global $APPLICATION;
$APPLICATION -> SetTitle(GetMessage("SUP_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$arFilterFormProps = array_merge(["bAdmin" => $bAdmin, "bDemo" => $bDemo, "sTableID" => $sTableID], $arFilterProps);

$facade -> getSubsystemFilterForm() -> createAndShowFilterForm($arFilterFormProps);

//ob_get_contents
$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
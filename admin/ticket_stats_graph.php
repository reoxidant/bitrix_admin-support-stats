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

list('bAdmin' => $bAdmin, 'bDemo' => $bDemo) = $facade -> getSubsystemRole() -> showAuthFormByRole(true);

$sTableID = $facade -> getSubsystemGraph() -> getGraph() -> addProperty("sTableID", 't_report_graph', true);

$facade -> getSubsystemCAdmin() -> initCAdminPropertyList($sTableID);

if ($facade -> getSubsystemCAdmin() -> getAdmin() -> IsDefaultFilter()):
    $defaultFilterValues = [
        'find_date1' => date('d.m.Y', strtotime("-30 day")),
        'find_open' => "Y",
        'find_close' => "Y",
        "find_all" => "Y",
        'find_mess' => "Y",
        'find_overdue_mess' => "Y",
    ];
else:
    $defaultFilterValues = null;
endif;

$arFilterFields = $facade -> getSubsystemCAdmin() -> getAdmin() -> addArFilterFields(true);

$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty("lAdmin") -> InitFilter($arFilterFields);

if ($bAdmin != "Y" && $bDemo != "Y") $find_responsible_id = $USER -> GetID();
InitBVar($find_responsible_exact_match);

if(!$set_filter && empty($defaultFilterValues)){
    foreach ($_SESSION["SESS_STATS"][$sTableID] as $key => $val){
        global $$key;
        if (isset($$key)) $$key = $val;
    }
}

$arFilterProps = [
    "find_site" => $find_site_stats,
    "find_date1" => $defaultFilterValues['find_date1'] ?? $find_date1_stats,
    "find_date2" => $find_date2_stats,
    "find_responsible_id" => $find_responsible_id_stats,
    "find_responsible" => $find_responsible_stats,
    "find_responsible_exact_match" => $find_responsible_exact_match,
    "find_sla_id" => $find_sla_id_stats,
    "find_category_id" =>  20,
    "find_criticality_id" => $find_criticality_id_stats,
    "find_status_id" => $find_status_id_stats,
    "find_mark_id" => $find_mark_id_stats,
    "find_source_id" => $find_source_id_stats,
    "find_open" => $defaultFilterValues['find_open'] ?? $find_open_stats,
    "find_close" => $defaultFilterValues['find_close'] ?? $find_close_stats,
    "find_all" => $defaultFilterValues['find_all'] ?? $find_all_stats,
    "find_mess" => $defaultFilterValues['find_mess'] ?? $find_mess_stats,
    "find_overdue_mess" => $defaultFilterValues['find_overdue_mess'] ?? $find_overdue_mess_stats,
];

$facade -> getSubsystemCAdmin() -> getAdmin() -> initSessionFilter($arFilterFields);

$facade -> getSubsystemCAdmin() -> getAdmin() -> addArFilterData($arFilterProps);

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
    $e->getMessage();
}

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

$arFilterFormProps = array_merge($arFilterFormProps, $arFilterProps);

$facade -> getSubsystemFilterForm() -> createAndShowFilterForm($arFilterFormProps);

//ob_get_contents
$facade -> getSubsystemCAdmin() -> getAdmin() -> getProperty('lAdmin') -> DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

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

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/admin/classes/Facade.php"); //смотри паттерн фасад

global $USER, $APPLICATION;

use admin\classes\Facade;
use admin\classes\SubsystemCAdminFilterStats;
use admin\classes\SubsystemCAdminStats;
use admin\classes\SubsystemFilterForm;
use admin\classes\SubsystemGraph;
use admin\classes\SubsystemRole;
use admin\classes\SubsystemSupportUser;
use admin\classes\SubsystemTicket;

$subsystemRole = new SubsystemRole();
$subsystemGraph = new SubsystemGraph();
$subsystemCAdminStats = new SubsystemCAdminStats();
$subsystemCAdminFilterStats = new SubsystemCAdminFilterStats();
$subsystemTicket = new SubsystemTicket();
$subsystemSupportUser = new SubsystemSupportUser();
$subsystemFilterForm = new SubsystemFilterForm();

$facade = new Facade(
    $subsystemRole,
    $subsystemGraph,
    $subsystemCAdminStats,
    $subsystemTicket,
    $subsystemSupportUser,
    $subsystemFilterForm
);

list('bAdmin' => $bAdmin, 'bDemo' => $bDemo) = $facade -> getSubsystemRole() -> showAuthFormByRole(true);

$sTableID = $facade -> getSubsystemGraph() -> getGraph() -> addProperty("sTableID", 't_report_graph', true);

$facade -> getSubsystemCAdminStats() -> initCAdminPropertyList($sTableID);

if ($facade -> getSubsystemCAdminStats() -> getAdmin() -> IsDefaultFilter()):
    $set_filter = "Y";
    $defaultFilterValues = [
        'find_date1' => date('d.m.Y', strtotime("-30 day")),
        'find_work_in' => "Y",
        'find_close_ticket' => "Y",
        'find_wait_answer_dit' => "Y",
        'find_wait_answer_user' => "Y",
    ];
else:
    $defaultFilterValues = null;
endif;

$arFilterFields = $facade -> getSubsystemCAdminStats() -> getAdmin() -> addArFilterFields(true);

$facade -> getSubsystemCAdminStats() -> getAdmin() -> getProperty("lAdmin") -> InitFilter($arFilterFields);

if (!$set_filter && empty($defaultFilterValues)) {
    foreach ($_SESSION["SESS_STATS"][$sTableID] as $key => $val) {
        global $$key;
        if (isset($$key)) $$key = $val;
    }
}

if ($bAdmin != "Y" && $bDemo != "Y") $find_responsible_id = $USER -> GetID();

InitBVar($find_responsible_exact_match);

/**
 * @param null $statusId
 * @param null $masterId
 * @return string
 */
function showStatusId($statusId = null, $masterId = null)
{
    return ($statusId == null) ? "Y" : ($statusId == $masterId) ? "Y" : "N";
}

$arFilterProps = [
    "find_site" => $find_site_stats,
    "find_date1" => $defaultFilterValues['find_date1'] ?? $find_date1_stats,
    "find_date2" => $find_date2_stats,
    "find_responsible_id" => $find_responsible_id,
    "find_responsible" => $find_responsible_stats,
    "find_responsible_exact_match" => $find_responsible_exact_match,
    "find_sla_id" => $find_sla_id_stats,
    "find_category_id" => 20,
    "find_criticality_id" => $find_criticality_id_stats,
    "find_status_id" => $find_status_id_stats,
    "find_mark_id" => $find_mark_id_stats,
    "find_source_id" => $find_source_id_stats,
    "find_work_in" => $defaultFilterValues['find_work_in'] ?? showStatusId($find_status_id_stats, 23),
    "find_close_ticket" => $defaultFilterValues['find_close_ticket'] ?? showStatusId($find_status_id_stats, 24),
    "find_wait_answer_dit" => $defaultFilterValues['find_wait_answer_dit'] ?? showStatusId($find_status_id_stats, 25),
    "find_wait_answer_user" => $defaultFilterValues['find_wait_answer_user'] ?? showStatusId($find_status_id_stats, 26),
    "find_mess" => $defaultFilterValues['find_mess'] ?? ($find_mess_stats ?? "Y"),
    "find_overdue_mess" => $defaultFilterValues['find_overdue_mess'] ?? ($find_overdue_mess_stats ?? "Y"),
];

$facade -> getSubsystemCAdminStats() -> getAdmin() -> initSessionFilter($arFilterFields);

$facade -> getSubsystemCAdminStats() -> getAdmin() -> addArFilterData($arFilterProps);

$arUsersID = $facade -> getSubsystemTicket() -> initTicketProperty($facade -> getSubsystemCAdminStats() -> getAdmin());

$facade -> getSubsystemSupportUser() -> addUsers($arUsersID);

//ob_start
$facade -> getSubsystemCAdminStats() -> getAdmin() -> getProperty('lAdmin') -> BeginCustomContent();
$facade -> getSubsystemCAdminStats() -> showErrorMessageIfExist(); ?>

    <!--HTML CONTENT-->

    <p><? echo GetMessage("SUP_SERVER_TIME") . "&nbsp;" . GetTime(time(), "FULL") ?></p>
    <h2><?= GetMessage("SUP_GRAPH_ALT") ?></h2>

    <!--HTML CONTENT-->

<?php

//Image
try {
    $facade -> getSubsystemGraph() -> createImage(
        $facade -> getSubsystemTicket() -> getTicket(),
        $facade -> getSubsystemCAdminStats() -> getAdmin(),
        $arrColor,
        $defaultFilterValues ?? $arFilterProps,
        "576",
        "400"
    );
} catch (\Exception $e) {
    $e -> getMessage();
}

$facade -> getSubsystemCAdminStats() -> getAdmin() -> getProperty("lAdmin") -> EndCustomContent();
$facade -> getSubsystemCAdminStats() -> getAdmin() -> getProperty("lAdmin") -> CheckListMode();

global $APPLICATION;
$APPLICATION -> SetTitle(GetMessage("SUP_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

//form
$arFilterFormProps = [
    "filter" => $facade -> getSubsystemCAdminStats() -> getAdmin() -> getProperty('filter'),
    "bAdmin" => $bAdmin,
    "bDemo" => $bDemo,
    "arrSupportUser" => $facade -> getSubsystemSupportUser() -> getArrSupportUser(),
    "sTableID" => $sTableID
];

$arFilterFormProps = array_merge($arFilterFormProps, $arFilterProps);

$facade -> getSubsystemFilterForm() -> createAndShowFilterForm($arFilterFormProps);

//ob_get_contents
$facade -> getSubsystemCAdminStats() -> getAdmin() -> getProperty('lAdmin') -> DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

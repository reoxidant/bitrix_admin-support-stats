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

$bDemo = (CTicket ::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket ::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket ::IsSupportTeam()) ? "Y" : "N";
$message = null;

if ($bAdmin != "Y" && $bSupportTeam != "Y" && $bDemo != "Y") $APPLICATION -> AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

require_once('classes/Graph.php');
require_once('classes/CAdminFilter.php');
require_once('classes/Ticket.php');
require_once('classes/SupportUser.php');
require_once('classes/FilterForm.php');

use admin\classes\CAdminFilter;
use admin\classes\Graph;
use admin\classes\Ticket;
use admin\classes\SupportUser;
use admin\classes\FilterForm;

$graph = new Graph();
$graph -> addProperty("sTableId", 't_report_graph');

list('sTableId' => $sTableId) = $graph -> getAllProperties();

$admin = new CAdminFilter();
$admin -> addProperty('oSort', new CAdminList($sTableId));
$admin -> addProperty('lAdmin', new CAdminList($sTableId, $admin -> getProperty('oSort')));

$arrMessages = array(
    GetMessage("SUP_F_SITE"),
    GetMessage("SUP_F_RESPONSIBLE"),
    GetMessage("SUP_F_SLA"),
    GetMessage("SUP_F_CATEGORY"),
    GetMessage("SUP_F_CRITICALITY"),
    GetMessage("SUP_F_STATUS"),
    GetMessage("SUP_F_MARK"),
    GetMessage("SUP_F_SOURCE"),
    GetMessage("SUP_SHOW")
);

$admin -> addProperty('filter', new CAdminList("filter_id", $arrMessages));

$admin -> setValDefaultFilter();

$admin -> setArFilterFields();

$admin -> getProperty('lAdmin') -> InitFilter($admin -> getProperty('arFilterFields'));

if ($bAdmin != "Y" && $bDemo != "Y") $find_responsible_id = $USER -> GetID();

InitBVar($find_responsible_exact_match);

$data_filter = [
    "find_site" => $find_site ?? null,
    "find_date1" => $find_date1 ?? null,
    "find_date2" => $find_date2 ?? null,
    "find_responsible_id" => $find_responsible_id ?? null,
    "find_responsible" => $find_responsible ?? null,
    "find_responsible_exact_match" => $find_responsible_exact_match ?? null,
    "find_sla_id" => $find_sla_id ?? null,
    "find_category_id" => $find_category_id ?? null,
    "find_criticality_id" => $find_criticality_id ?? null,
    "find_status_id" => $find_status_id ?? null,
    "find_mark_id" => $find_mark_id ?? null,
    "find_source_id" => $find_source_id ?? null
];

$admin -> addArrFilter($data_filter);

$message = ($admin->error ?? null) ? $admin->error : null;

$tickets = new Ticket();

$tickets->addListTicketsByFilterPropertyDB("rsTickets", $admin->getProperty('arFilter'));

$tickets->addDefaultPropertyByKeys("arrTime", ["1", "1_2", "2_3", "3_4", "4_5", "5_6", "6_7", "7"], 0);
$tickets->addDefaultPropertyByKeys("arrMess", ["2_m", "3_m", "4_m", "5_m", "6_m", "7_m", "8_m", "9_m", "10_m"], 0);

$tickets->fillOutTickets('rsTickets');

$user = new SupportUser($tickets->arTicketUsersID);
$user->addSupportUsers();

//ob_start
$admin -> getProperty('lAdmin') -> BeginCustomContent();

if ($message)
    echo $message -> Show();
?>
<!--Время на сервере:  17.08.2020 16:12:21-->
<p><? echo GetMessage("SUP_SERVER_TIME") . "&nbsp;" . GetTime(time(), "FULL") ?></p>
<!--Нагрузка на техподдержку-->
<h2><?= GetMessage("SUP_GRAPH_ALT") ?></h2>

<!--Graph-->
<?php $graph->createImageGraph(
        $tickets->getProperty('show_graph'), $admin -> getProperty('arFilterFields'),
        $admin->getProperty('lAdmin')->getFilter() ?? ($admin->getProperty('defaultFilterValues') ?? null),
$arrColor ?? null
    );

//ob_clean
$admin -> getProperty("lAdmin") -> EndCustomContent();

$admin -> getProperty("lAdmin") -> CheckListMode();

$APPLICATION -> SetTitle(GetMessage("SUP_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

/*$filterForm = new FilterForm();
$filterForm->addProperty('filter', $filter);
$filterForm->addProperty('find_site', $find_site);
$filterForm->addProperty('find_date1', $find_date1);
$filterForm->addProperty('find_date2', $find_date2);
$filterForm->addProperty('bAdmin', $bAdmin);
$filterForm->addProperty('bDemo', $bDemo);
$filterForm->addProperty('arrSupportUser', $arrSupportUser);
$filterForm->addProperty('find_responsible', $find_responsible);
$filterForm->addProperty('find_responsible_id', $find_responsible_id);
$filterForm->addProperty('find_responsible_exact_match', $find_responsible_exact_match);
$filterForm->addProperty('find_criticality_id', $find_criticality_id);
$filterForm->addProperty('find_status_id', $find_status_id);
$filterForm->addProperty('find_mark_id', $find_mark_id);
$filterForm->addProperty('find_source_id', $find_source_id);
$filterForm->addProperty('find_open', $find_open);
$filterForm->addProperty('find_close', $find_close);
$filterForm->addProperty('find_all', $find_all);
$filterForm->addProperty('find_sla_id', $find_sla_id);
$filterForm->addProperty('find_mess', $find_mess);
$filterForm->addProperty('find_overdue_mess', $find_overdue_mess);
$filterForm->addProperty('find_category_id', $find_category_id);
$filterForm->addProperty('sTableID', $sTableID);
$filterForm->createFilterForm();*/

//ob_get_contents
$admin -> getProperty('lAdmin') -> DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

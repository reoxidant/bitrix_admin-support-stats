<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");

$bDemo = (CTicket::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket::IsSupportTeam()) ? "Y" : "N";
$message = null;

if($bAdmin!="Y" && $bSupportTeam!="Y" && $bDemo!="Y") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

require_once('classes/Graph.php');
require_once('classes/CAdminFilter.php');

use admin\classes\Graph;
use admin\classes\CAdminFilter;

$graph = new Graph();
$graph->addProperty("sTableId", 't_report_graph');

list('sTableId' => $sTableId) = $graph->getAllProperties();

$admin = new CAdminFilter();
$admin -> addProperty('oSort', new CAdminList($sTableId));
$admin -> addProperty('lAdmin', new CAdminList($sTableId, $admin->getProperty('oSort')));

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

$admin->setValDefaultFilter();

$admin -> getProperty('lAdmin') -> InitFilter($admin->getProperty('arFilterFields'));

if ($bAdmin!="Y" && $bDemo!="Y") $find_responsible_id = $USER->GetID();

InitBVar($find_responsible_exact_match);

$admin -> addArrFilter();

$rsTickets = CTicket::GetList($by, $order, $admin->getProperty('arFilter'), $is_filtered, "Y", "N", "N");

$OPEN_TICKETS = $CLOSE_TICKETS = 0;
$arrTickets = array();
$arrTime = array();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>

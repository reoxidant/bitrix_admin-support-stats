<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$arFilter = Array(
    "SITE"						=> $find_site,
    "DATE_CREATE_1"				=> $find_date1,
    "DATE_CREATE_2"				=> $find_date2,
    "RESPONSIBLE_ID"			=> $find_responsible_id,
    "RESPONSIBLE"				=> $find_responsible,
    "RESPONSIBLE_EXACT_MATCH"	=> $find_responsible_exact_match,
    "SLA"						=> $find_sla_id,
    "CATEGORY"					=> $find_category_id,
    "CRITICALITY"				=> $find_criticality_id,
    "STATUS"					=> $find_status_id,
    "MARK"						=> $find_mark_id,
    "SOURCE"					=> $find_source_id,
);


$rsTickets = CTicket::GetList($by, $order, $arFilter, $is_filtered, "Y", "N", "N");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>

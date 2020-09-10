<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");

$bDemo = (CTicket ::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket ::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket ::IsSupportTeam()) ? "Y" : "N";

if ($bAdmin != "Y" && $bSupportTeam != "Y" && $bDemo != "Y") $APPLICATION -> AuthForm(GetMessage("ACCESS_DENIED"));

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

// создаем изображение
$ImageHendle = CreateImageHandle($width, $height);

$arrX = array(); // массив точек графика по X
$arrY = array(); // массив точек графика по Y
$arrayX = array(); // массив точек на оси X (деления)
$arrayY = array(); // массив точек на оси Y (деления)

/******************************************************
 * Собираем точки графика
 *******************************************************/

$arFilter = array(
    "SITE" => $find_site,
    "DATE_CREATE_1" => $find_date1,
    "DATE_CREATE_2" => $find_date2,
    "RESPONSIBLE_ID" => $find_responsible_id,
    "RESPONSIBLE" => $find_responsible,
    "RESPONSIBLE_EXACT_MATCH" => $find_responsible_exact_match,
    "SLA" => $find_sla_id,
    "CATEGORY" => $find_category_id,
    "CRITICALITY" => $find_criticality_id,
    "STATUS" => $find_status_id,
    "MARK" => $find_mark_id,
    "SOURCE" => $find_source_id,
);
$rsTickets = CTicket ::GetDynamicList($by = "s_date_create", $order = "asc", $arFilter);
while ($rsTickets -> ExtractFields("f_", false)) {
    // Получаем текущее время
    $date = mktime(0, 0, 0, $f_CREATE_MONTH, $f_CREATE_DAY, $f_CREATE_YEAR);
    $date_tmp = 0;
    // если даты пропущены (идут не по порядку) то
    $next_date = AddTime($prev_date, 1, "D");
    if ($date > $next_date && intval($prev_date) > 0) {
        // заполняем пропущенные даты, если они есть
        $date_tmp = $next_date;
        while ($date_tmp < $date) {
            $arrX[] = $date_tmp;
            $arrY_all[] = 0;
            $date_tmp = AddTime($date_tmp, 1, "D");
        }
    }
    $arrX[] = $date;
    //Сколько заявок на оси Y
    //Всех
    $arrY_all[] = intval($f_ALL_TICKETS);
    $prev_date = $date;
}
/******************************************************
 * Формируем ось X
 *******************************************************/

$arrayX = GetArrayX($arrX, $MinX, $MaxX);

if ($find_mess == "Y" || $find_overdue_mess == "Y") {
    $arFilter = array(
        "SITE" => $find_site,
        "DATE_CREATE_1" => $find_date1,
        "DATE_CREATE_2" => $find_date2,
        "OWNER_ID" => $find_responsible_id,
        "OWNER" => $find_responsible,
        "OWNER_EXACT_MATCH" => $find_responsible_exact_match,
        "SLA" => $find_sla_id,
        "CATEGORY" => $find_category_id,
        "CRITICALITY" => $find_criticality_id,
        "STATUS" => $find_status_id,
        "MARK" => $find_mark_id,
        "SOURCE" => $find_source_id,
        "IS_HIDDEN" => "N",
        "IS_LOG" => "N",
        "IS_OVERDUE" => "N"
    );
    $rsMess = CTicket ::GetMessageDynamicList($v1 = "s_date_create", $v2 = "asc", $arFilter);
    while ($arMess = $rsMess -> Fetch()) {
        $date = mktime(0, 0, 0, $arMess["CREATE_MONTH"], $arMess["CREATE_DAY"], $arMess["CREATE_YEAR"]);
        $arrMessages[$date] = $arMess["COUNTER"];
        $arrOverdueMessages[$date] = $arMess["COUNTER_OVERDUE"];
    }
    foreach ($arrX as $t) {
        $arrY_mess[] = intval($arrMessages[$t]);
        $arrY_overdue_mess[] = intval($arrOverdueMessages[$t]);
    }
}

/******************************************************
 * Формируем ось Y
 *******************************************************/
$arrY = array();
$arrY = array_merge($arrY, $arrY_all);
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

/******************************************************
 * Рисуем координатную сетку
 *******************************************************/

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHendle);

/******************************************************
 * Рисуем графики
 *******************************************************/

$arrColor = ["8080C0", "FF0000", "0000FF", "00CE67", "F2BE0D", "FB0600"];

Graf($arrX, $arrY_all, $ImageHendle, $MinX, $MaxX, $MinY, $MaxY, $arrColor[$indexOfStatus]);

/******************************************************
 * Отображаем изображение
 *******************************************************/

ShowImageHeader($ImageHendle);
?>
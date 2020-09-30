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
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/muiv.support/include.php");

$bDemo = (CTicket ::IsDemo()) ? "Y" : "N";
$bAdmin = (CTicket ::IsAdmin()) ? "Y" : "N";
$bSupportTeam = (CTicket ::IsSupportTeam()) ? "Y" : "N";

if ($bAdmin != "Y" && $bSupportTeam != "Y" && $bDemo != "Y") $APPLICATION -> AuthForm(GetMessage("ACCESS_DENIED"));

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/muiv.support/colors.php");
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

$find_all = "Y";
$find_open = "N";
$find_close = "N";
$find_mess = "N";
$find_overdue_mess = "N";

$z = CTicketDictionary ::GetDropDown("S");
$DataMinY = null;
$DataMaxY = null;

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
    "STATUS"					=> null,
    "MARK"						=> $find_mark_id,
    "SOURCE"					=> $find_source_id,
);
$rsTickets = CTicket ::GetDynamicList($by = "s_date_create", $order = "asc", $arFilter);
while ($rsTickets->ExtractFields("f_",false))
{
    $date = mktime(0,0,0, $f_CREATE_MONTH, $f_CREATE_DAY, $f_CREATE_YEAR);
    $date_tmp = 0;
    // если даты пропущены (идут не по порядку) то
    $next_date = AddTime($prev_date_data,1,"D");
    if ($date>$next_date && intval($prev_date_data)>0)
    {
        // заполняем пропущенные даты
        $date_tmp = $next_date;
        while ($date_tmp<$date)
        {
            $arrX_main[] = $date_tmp;
            $arrY_all_main[] = 0;
            $date_tmp = AddTime($date_tmp,1,"D");
        }
    }
    $arrX_main[] = $date;
    $arrY_all_main[] = intval($f_ALL_TICKETS);
    $prev_date_data = $date;
}

//Цифры по X
$arrayX = GetArrayX($arrX_main, $MinX, $MaxX);

$arrY = array();
$arrY = array_merge($arrY, $arrY_all_main);
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

$DataMinY = $MinY;
$DataMaxY = $MaxY;

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHendle);

foreach ($arrColor as $value){
    $colors[] = $value;
}

//below is working
/*
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
    "STATUS"					=> 10,
    "MARK"						=> $find_mark_id,
    "SOURCE"					=> $find_source_id,
);

$rsTickets = CTicket ::GetDynamicList($by = "s_date_create", $order = "asc", $arFilter);

//Собираем данные из результата базы данных
while ($rsTickets->ExtractFields("f_",false))
{
    $date = mktime(0,0,0, $f_CREATE_MONTH, $f_CREATE_DAY, $f_CREATE_YEAR);
    $date_tmp = 0;
    // если даты пропущены (идут не по порядку) то
    $next_date = AddTime($prev_date,1,"D");
    if ($date>$next_date && intval($prev_date)>0)
    {
        // заполняем пропущенные даты
        $date_tmp = $next_date;
        while ($date_tmp<$date)
        {
            $arrX_all[] = $date_tmp;
            $arrY_all[] = 0;
            $date_tmp = AddTime($date_tmp,1,"D");
        }
    }
    $arrX_all[] = $date;
    $arrY_all[] = intval($f_ALL_TICKETS);
    $prev_date = $date;
}

//Цифры по X
$arrayX = GetArrayX($arrX_all, $MinX, $MaxX);

//$arrayX - даты

//How to create lines on the graph

$arrY_all = [1,1,0,0,0,0,1];

Graf($arrX_all, $arrY_all, $ImageHendle, $MinX, $MaxX, $DataMinY, $DataMaxY, $colors[2]);

//$arrY_all = [1,1,2,0,2,1,1];

//Graf($arrX_all, $arrY_all, $ImageHendle, $MinX, $MaxX, $DataMinY, $DataMaxY, $colors[1]);
*/

ShowImageHeader($ImageHendle);
?>
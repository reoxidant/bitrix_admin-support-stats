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
while ($zr = $z -> Fetch()) {
    $arStatus[] = $zr['ID'];
}



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
            $arrX[] = $date_tmp;
            $arrY_all[] = 0;
            $date_tmp = AddTime($date_tmp,1,"D");
        }
    }
    $arrX[] = $date;
    $arrY_all[] = intval($f_ALL_TICKETS);
    $prev_date = $date;
}

//Цифры по X
$arrayX = GetArrayX($arrX, $MinX, $MaxX);

//Цифры по Y
$arrY = array();
$arrY = array_merge($arrY, $arrY_all);
$arrayY = GetArrayY($arrY, $MinY, $MaxY);

DrawCoordinatGrid($arrayX, $arrayY, $width, $height, $ImageHendle);

foreach ($arrColor as $key => $value){
    $colors[] = $value;
}
Graf($arrX, $arrY_all, $ImageHendle, $MinX, $MaxX, $MinY, $MaxY, $colors[array_search($find_status_id, $arStatus)]);

ShowImageHeader($ImageHendle);
?>
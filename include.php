<?
global $DB, $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/filter_tools.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/muiv.support/errors.php");

$db_type = strtolower($DB -> type);

CModule ::AddAutoloadClasses(
    "muiv.support",
    array(
        "CTicket" => "classes/" . $db_type . "/support.php",
        "CTicketDictionary" => "classes/" . $db_type . "/dictionary.php",
    )
);

?>
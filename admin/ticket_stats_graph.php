<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/prolog.php");

require_once('classes/Facade.php');

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
manageAllOperation($facade);

function manageAllOperation(Facade $facade)
{
    list(
        'bDemo' => $bDemo,
        'bAdmin' => $bAdmin
    ) = $facade -> getSubsystemRole() -> showAuthFormByRole();

    list('sTableID' => $sTableID) = $facade -> getSubsystemGraph() -> initGraphPropertyAndReturnVal();

    $facade -> getSubsystemCAdmin() -> initCAdminPropertyList($sTableID);

    $facade -> getSubsystemCAdmin() -> addToPropertyCommonFilterValues();
    $facade -> getSubsystemCAdmin() -> initFilter();
    $facade -> getSubsystemCAdmin() -> addToPropertyArFilter();
    $facade -> getSubsystemCAdmin() -> showErrorMessageIfExist();

    list(
        'find_responsible_id' => $find_responsible_id,
        'find_responsible_exact_match' => $find_responsible_exact_match,
        'find_open' => $find_open,
        'find_close' => $find_close,
        'find_all' => $find_all,
        'find_sla_id' => $find_sla_id,
        'find_mess' => $find_mess,
        'find_overdue_mess' => $find_overdue_mess
    ) = $facade -> getSubsystemCAdmin() -> getFindList($bAdmin, $bDemo);

    $arTicketUsersID = $facade -> getSubsystemTicket() -> initTicketPropertyAndReturnVal($facade->getSubsystemCAdmin()->getAdmin());

    $facade-> getSubsystemSupportUser() -> addUsers($arTicketUsersID);
}

//ob_start
//$admin -> getProperty('lAdmin') -> BeginCustomContent();

?>

<!--Время на сервере:  17.08.2020 16:12:21-->
<p><? echo GetMessage("SUP_SERVER_TIME") . "&nbsp;" . GetTime(time(), "FULL") ?></p>
<!--Нагрузка на техподдержку-->
<h2><?= GetMessage("SUP_GRAPH_ALT") ?></h2>

<?php

//$admin -> getProperty("lAdmin") -> EndCustomContent();

//$admin -> getProperty("lAdmin") -> CheckListMode();

$APPLICATION -> SetTitle(GetMessage("SUP_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

//ob_get_contents
//$admin -> getProperty('lAdmin') -> DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

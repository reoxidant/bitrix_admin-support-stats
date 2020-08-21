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

    list(
        'find_responsible_id' => $find_responsible_id,
        'find_responsible_exact_match' => $find_responsible_exact_match,
        'find_open' => $find_open,
        'find_close' => $find_close,
        'find_all' => $find_all,
        'find_sla_id' => $find_sla_id,
        'find_mess' => $find_mess,
        'find_overdue_mess' => $find_overdue_mess,
        'find_site' => $find_site,
        'find_date1' => $find_date1,
        'find_date2' => $find_date2,
        'find_responsible' => $find_responsible,
        'find_category_id' => $find_category_id,
        'find_criticality_id' => $find_criticality_id,
        'find_status_id' => $find_status_id,
        'find_mark_id' => $find_mark_id,
        'find_source_id' =>   $find_source_id
    ) = $facade -> getSubsystemCAdmin() -> getFindList($bAdmin, $bDemo);

    $arTicketUsersID = $facade -> getSubsystemTicket() -> initTicketPropertyAndReturnVal($facade->getSubsystemCAdmin()->getAdmin());

    $facade-> getSubsystemSupportUser() -> addUsers($arTicketUsersID);
    $arrSupportUser = $facade->getSubsystemSupportUser()->getArrSupportUser();

    //ob_start
    $facade->getSubsystemCAdmin()->getAdmin() -> getProperty('lAdmin') -> BeginCustomContent();

    $facade -> getSubsystemCAdmin() -> showErrorMessageIfExist();
?>

    <!--Время на сервере:  17.08.2020 16:12:21-->
    <p><? echo GetMessage("SUP_SERVER_TIME") . "&nbsp;" . GetTime(time(), "FULL") ?></p>
    <!--Нагрузка на техподдержку-->
    <h2><?= GetMessage("SUP_GRAPH_ALT") ?></h2>

<?php
    $facade->getSubsystemGraph()->createImage(
        $facade->getSubsystemGraph()->getGraph(),
        $facade->getSubsystemCAdmin()->getAdmin(),
        "576",
        "400"
    );

    $facade->getSubsystemCAdmin()->getAdmin() -> getProperty("lAdmin") -> EndCustomContent();

    $facade->getSubsystemCAdmin()->getAdmin() -> getProperty("lAdmin") -> CheckListMode();

    global $APPLICATION;
    $APPLICATION -> SetTitle(GetMessage("SUP_PAGE_TITLE"));

    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    $arFilterFormProps = [
        "APPLICATION" => $APPLICATION,
        "filter" => $facade->getSubsystemCAdmin()->getAdmin()->getProperty('filter'),
        "find_site" => $find_site,
        "find_date1" => $find_date1,
        "find_date2" => $find_date2,
        "bAdmin" => $bAdmin,
        "bDemo" => $bDemo,
        "arrSupportUser" => $arrSupportUser,
        "find_responsible" => $find_responsible,
        "find_responsible_id" => $find_responsible_id,
        "find_responsible_exact_match" => $find_responsible_exact_match,
        "find_criticality_id" => $find_criticality_id,
        "find_status_id" => $find_status_id,
        "find_mark_id" => $find_mark_id,
        "find_source_id" => $find_source_id,
        "find_open" => $find_open,
        "find_close" => $find_close,
        "find_all" => $find_all,
        "find_sla_id" => $find_sla_id,
        "find_mess" => $find_mess,
        "find_overdue_mess" => $find_overdue_mess,
        "find_category_id" => $find_category_id,
        "sTableID" => $sTableID
    ];
    $facade->getSubsystemFilterForm()->initFilterFormProperty($arFilterFormProps);

    //ob_get_contents
    $facade->getSubsystemCAdmin()->getAdmin() -> getProperty('lAdmin') -> DisplayList();

    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}
?>

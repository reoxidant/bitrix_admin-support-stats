<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

require_once('Graph.php');
require_once('CAdmin.php');
require_once('Ticket.php');
require_once('SupportUser.php');
require_once('FilterForm.php');

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/include.php");
IncludeModuleLangFile(__FILE__);

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/support/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

use CAdminList;
use CAdminSorting;
use CTicket;

interface systemArParams{
    public function setSystemParams($name, $value);
    public function getSystemParams($name);
}

/**
 * Class Facade
 * @package admin\classes
 */
class Facade
{
    /**
     * @var SubsystemRole|null
     */
    private $subsystemRole;
    /**
     * @var SubsystemGraph|null
     */
    private $subsystemGraph;
    /**
     * @var SubsystemCAdmin|null
     */
    private $subsystemCAdmin;

    /**
     * @var SubsystemTicket|null
     */
    private $subsystemTicket;

    /**
     * @var SubsystemSupportUser|SubsystemTicket|null
     */
    private $subsystemSupportUser;

    /**
     * @var SubsystemFilterForm|SubsystemTicket|null
     */
    private $subsystemFilterForm;

    /**
     * Facade constructor.
     * @param SubsystemRole|null $subsystemRole
     * @param SubsystemGraph|null $subsystemGraph
     * @param SubsystemCAdmin|null $subsystemCAdmin
     * @param SubsystemTicket|null $subsystemTicket
     * @param SubsystemSupportUser|null $subsystemSupportUser
     * @param SubsystemFilterForm|null $subsystemFilterForm
     */
    public function __construct(
        SubsystemRole $subsystemRole = null,
        SubsystemGraph $subsystemGraph = null,
        SubsystemCAdmin $subsystemCAdmin = null,
        SubsystemTicket $subsystemTicket = null,
        SubsystemSupportUser $subsystemSupportUser = null,
        SubsystemFilterForm $subsystemFilterForm = null
    )
    {
        $this -> subsystemRole = $subsystemRole ?: new SubsystemRole();
        $this -> subsystemGraph = $subsystemGraph ?: new SubsystemGraph();
        $this -> subsystemCAdmin = $subsystemCAdmin ?: new SubsystemCAdmin();
        $this -> subsystemTicket = $subsystemTicket ?: new SubsystemTicket();
        $this -> subsystemSupportUser = $subsystemSupportUser ?: new SubsystemSupportUser();
        $this -> subsystemFilterForm = $subsystemFilterForm ?: new SubsystemFilterForm();
    }

    /**
     *
     */
    public function operation()
    {

    }

    /**
     * @return SubsystemRole|null
     */
    public function getSubsystemRole(): ?SubsystemRole
    {
        return $this -> subsystemRole;
    }

    /**
     * @return SubsystemGraph|null
     */
    public function getSubsystemGraph(): ?SubsystemGraph
    {
        return $this -> subsystemGraph;
    }

    /**
     * @return SubsystemCAdmin|null
     */
    public function getSubsystemCAdmin(): ?SubsystemCAdmin
    {
        return $this -> subsystemCAdmin;
    }

    /**
     * @return SubsystemTicket|null
     */
    public function getSubsystemTicket(): ?SubsystemTicket
    {
        return $this -> subsystemTicket;
    }

    /**
     * @return SubsystemSupportUser|SubsystemTicket|null
     */
    public function getSubsystemSupportUser()
    {
        return $this -> subsystemSupportUser;
    }

    /**
     * @return SubsystemFilterForm|SubsystemTicket|null
     */
    public function getSubsystemFilterForm()
    {
        return $this -> subsystemFilterForm;
    }
}

/**
 * Class SubsystemRole
 * @package admin\classes
 */
class SubsystemRole implements systemArParams
{
    public $arSystemParamsRole = [];
    /**
     *
     */
    public function showAuthFormByRole()
    {
        global $APPLICATION;
        $bDemo = (CTicket ::IsDemo()) ? "Y" : "N";
        $bAdmin = (CTicket ::IsAdmin()) ? "Y" : "N";
        $bSupportTeam = (CTicket ::IsSupportTeam()) ? "Y" : "N";
        if ($bAdmin != "Y" && $bSupportTeam != "Y" && $bDemo != "Y") {
            $APPLICATION -> AuthForm(GetMessage("ACCESS_DENIED"));
        }
        $this->setSystemParams('authRole', ["bAdmin" => $bAdmin, "bSupportTeam" => $bSupportTeam]);
    }

    function setSystemParams($name, $value)
    {
        $this->arSystemParamsRole[$name] = $value;
    }

    function getSystemParams($name)
    {
        return $this->arSystemParamsRole[$name];
    }
}

/**
 * Class SubsystemGraph
 * @package admin\classes
 */
class SubsystemGraph implements systemArParams
{
    private $arSystemParamsGraph = [];
    /**
     * @var Graph|null
     */
    private $graph;

    private $arrColorInc;

    /**
     * SubsystemGraph constructor.
     * @param Graph|null $graph
     * @param $arrColor
     */
    public function __construct(
        Graph $graph = null
    )
    {
        $this -> graph = $graph ?: new Graph();
    }

    /**
     *
     */
    public function initGraphProperty()
    {
        $this -> graph -> addProperty("sTableID", 't_report_graph');

        $this->setSystemParams("sTableID", $this -> graph -> getProperty("sTableID"));
    }

    /**
     * @param $ticket
     * @param $admin
     * @param $width
     * @param $height
     */
    public function createImage($ticket, $admin, $arrColorInc, $width = null, $height = null)
    {
        $this -> graph -> createImageGraph(
            $ticket -> getProperty('show_graph'), $admin -> getProperty('arFilterFields'),
            $admin -> getProperty('lAdmin') -> getFilter() ?? ($admin -> getProperty('defaultFilterValues') ?? null),
            $arrColorInc ?? null,
            $width,
            $height
        );
    }

    public function setSystemParams($name, $value)
    {
        $this->arSystemParamsGraph[$name] = $value;
    }

    public function getSystemParams($name)
    {
        return $this->arSystemParamsGraph[$name];
    }
}

/**
 * Class SubsystemCAdmin
 * @package admin\classes
 */
class SubsystemCAdmin implements systemArParams
{
    private $arSystemParamsCAdmin = [];

    /**
     * @var CAdmin|null
     */
    private $admin;

    /**
     * SubsystemCAdmin constructor.
     * @param CAdmin|null $admin
     */
    public function __construct(
        CAdmin $admin = null
    )
    {
        $this -> admin = $admin ?: new CAdmin();
    }

    /**
     * @param $sTableID
     * @param $arrMessages
     */
    public function initCAdminPropertyList($sTableID)
    {
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
        $this -> admin -> addProperty('oSort', new CAdminSorting($sTableID));
        $this -> admin -> addProperty('lAdmin', new CAdminList($sTableID, $this -> admin -> getProperty('oSort')));
        $this -> admin -> addProperty('filter', new CAdminList("filter_id", $arrMessages));
    }

    /**
     *
     */
    public function addToPropertyCommonFilterValues()
    {
        $this -> admin -> addValDefaultFilter();
        $this -> admin -> addArFilterFields();
    }

    /**
     *
     */
    public function initFilter()
    {
        $this -> admin -> getProperty('lAdmin') -> InitFilter($this -> admin -> getProperty('arFilterFields'));
    }

    /**
     *
     */
    public function addToPropertyArFilter()
    {
        $data_filter = [
            "find_site" => $find_site ?? null,
            "find_date1" => $find_date1 ?? null,
            "find_date2" => $find_date2 ?? null,
            "find_responsible_id" => $find_responsible_id ?? null,
            "find_responsible" => $find_responsible ?? null,
            "find_responsible_exact_match" => $find_responsible_exact_match ?? null,
            "find_sla_id" => $find_sla_id ?? null,
            "find_category_id" => $finds_category_id ?? null,
            "find_criticality_id" => $find_criticality_id ?? null,
            "find_status_id" => $find_status_id ?? null,
            "find_mark_id" => $find_mark_id ?? null,
            "find_source_id" => $find_source_id ?? null
        ];

        $this -> admin -> addArFilterData($data_filter);
    }

    /**
     *
     */
    public function showErrorMessageIfExist()
    {
        if ($this -> admin -> error) {
            $this -> admin -> error -> Show();
        }
    }

    /**
     * @param $authRole
     * @return array
     */
    public function recordFindList($authRole)
    {
        list($bAdmin, $bDemo) = $authRole;

        global $USER;

        list(
            'find_date1_DAYS_TO_BACK' => $find_date1_DAYS_TO_BACK,
            'find_open' => $find_open,
            'find_close' => $find_close,
            'find_all' => $find_all,
            'find_mess' => $find_mess,
            'find_overdue_mess' => $find_overdue_mess,
            'set_filter' => $set_filter,
            'find_site' => $find_site,
            "find_responsible" => $find_responsible,
            "find_responsible_id" => $find_responsible_id,
            "find_responsible_exact_match" => $find_responsible_exact_match,
            "find_category_id" => $find_category_id,
            "find_criticality_id" => $find_criticality_id,
            "find_status_id" => $find_status_id,
            "find_sla_id" => $find_sla_id,
            "find_mark_id" => $find_mark_id,
            "find_source_id" => $find_source_id,
            "find_date1" => $find_date1,
            "find_date2" => $find_date2
            ) = $this -> admin -> getProperty('lAdmin') -> getFilter() ?? ($this -> admin -> getProperty('defaultFilterValues') ?? null);

        $this->setSystemParams('findList', [
            'find_date1_DAYS_TO_BACK' => $find_date1_DAYS_TO_BACK ?? null,
            'find_open' => $find_open ?? null,
            'find_close' => $find_close ?? null,
            'find_all' => $find_all ?? null,
            'find_mess' => $find_mess ?? null,
            'find_overdue_mess' => $find_overdue_mess ?? null,
            'set_filter' => $set_filter ?? null,
            'find_site' => $find_site ?? null,
            "find_responsible" => $find_responsible ?? null,
            "find_responsible_id" => $find_responsible_id ?? ($bAdmin != "Y" && $bDemo != "Y") ? $find_responsible_id = $USER -> GetID() : null,
            "find_responsible_exact_match" => $find_responsible_exact_match ?? InitBVar($find_responsible_exact_match),
            "find_category_id" =>  $find_category_id ?? null,
            "find_criticality_id" => $find_criticality_id ?? null,
            "find_status_id" => $find_status_id ?? null,
            "find_sla_id" => $find_sla_id ?? null,
            "find_mark_id" => $find_mark_id ?? null,
            "find_source_id" => $find_source_id ?? null,
            "find_date1" => $find_date1 ?? null,
            "find_date2" => $find_date2 ?? null
        ]);
    }

    /**
     * @return CAdmin|null
     */
    public function getAdmin(): ?CAdmin
    {
        return $this -> admin;
    }

    public function setSystemParams($name, $value)
    {
        $this->arSystemParamsCAdmin[$name] = $value;
    }

    public function getSystemParams($name)
    {
        return $this->arSystemParamsCAdmin[$name];
    }
}

/**
 * Class SubsystemTicket
 * @package admin\classes
 */
class SubsystemTicket implements systemArParams
{
    private $arSystemParamsTicket = [];

    /**
     * @var Ticket|null
     */
    private $ticket;

    /**
     * SubsystemTicket constructor.
     * @param Ticket|null $ticket
     */
    public function __construct(
        Ticket $ticket = null
    )
    {
        $this -> ticket = $ticket ?: new Ticket();
    }

    /**
     * @param $admin
     */
    public function initTicketProperty($admin)
    {
        $this -> ticket -> addListTicketsDB("rsTickets", $admin -> getProperty('arFilter'));
        $this -> ticket -> addDefaultPropertyByKeys("arrTime", ["1", "1_2", "2_3", "3_4", "4_5", "5_6", "6_7", "7"], 0);
        $this -> ticket -> addDefaultPropertyByKeys("arrMess", ["2_m", "3_m", "4_m", "5_m", "6_m", "7_m", "8_m", "9_m", "10_m"], 0);
        $this -> ticket -> addAdditionalDataInto('rsTickets');
        $this->setSystemParams("arTicketUsersID", $this -> ticket -> arTicketUsersID);
    }

    public function setSystemParams($name, $value)
    {
        $this->arSystemParamsTicket[$name] = $value;
    }

    public function getSystemParams($name)
    {
        return $this->arSystemParamsTicket[$name];
    }

    /**
     * @return Ticket|null
     */
    public function getTicket(): ?Ticket
    {
        return $this -> ticket;
    }
}

/**
 * Class SubsystemSupportUser
 * @package admin\classes
 */
class SubsystemSupportUser
{
    /**
     * @var SupportUser|null
     */
    private $supportUser;

    /**
     * SubsystemSupportUser constructor.
     * @param SupportUser|null $supportUser
     * @param $ticket
     */
    public function __construct(
        SupportUser $supportUser = null
    )
    {
        $this -> supportUser = $supportUser ?: new SupportUser();
    }

    /**
     * @param $arTicketUsersID
     */
    public function addUsers($arTicketUsersID)
    {
        $this -> supportUser -> setArrSupportUser($arTicketUsersID);
        $this -> supportUser -> setSupportsUsersID($arTicketUsersID);
        $this -> supportUser -> addSupportUsers();
    }

    /**
     * @return mixed
     */
    public function getArrSupportUser()
    {
        return $this -> supportUser->getArrSupportUser();
    }
}

/**
 * Class SubsystemFilterForm
 * @package admin\classes
 */
class SubsystemFilterForm
{
    /**
     * @var FilterForm|null
     */
    private $filterForm;

    /**
     * SubsystemFilterForm constructor.
     * @param FilterForm|null $filterForm
     */
    public function __construct(
        FilterForm $filterForm = null
    )
    {
        $this -> filterForm = $filterForm ?: new FilterForm();
    }

    /**
     * @param $arrayProps
     */
    public function initFilterFormProperty($arrayProps)
    {
        foreach ($arrayProps as $key => $prop) {
            $this -> filterForm -> addProperty($key, $prop ?? null);
        }
    }

    /**
     *
     */
    public function createAndShowFilterForm()
    {
        $this -> filterForm -> generateFilterForm();
    }
}
<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

require_once('Graph.php');
require_once('CAdminStats.php');
require_once('CAdminFilterStats.php');
require_once('Ticket.php');
require_once('SupportUser.php');
require_once('FilterForm.php');

use CAdminList;
use CAdminSorting;
use CTicket;

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
     * @var SubsystemCAdminStats|null
     */
    private $subsystemCAdminStats;

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
     * @param SubsystemCAdminStats|null $subsystemCAdminStats
     * @param SubsystemCAdminFilterStats|null $subsystemCAdminFilterStats
     * @param SubsystemTicket|null $subsystemTicket
     * @param SubsystemSupportUser|null $subsystemSupportUser
     * @param SubsystemFilterForm|null $subsystemFilterForm
     */
    public function __construct(
        SubsystemRole $subsystemRole = null,
        SubsystemGraph $subsystemGraph = null,
        SubsystemCAdminStats $subsystemCAdminStats = null,
        SubsystemTicket $subsystemTicket = null,
        SubsystemSupportUser $subsystemSupportUser = null,
        SubsystemFilterForm $subsystemFilterForm = null
    )
    {
        $this -> subsystemRole = $subsystemRole ?: new SubsystemRole();
        $this -> subsystemGraph = $subsystemGraph ?: new SubsystemGraph();
        $this -> subsystemCAdminStats = $subsystemCAdminStats ?: new SubsystemCAdminStats();
        $this -> subsystemTicket = $subsystemTicket ?: new SubsystemTicket();
        $this -> subsystemSupportUser = $subsystemSupportUser ?: new SubsystemSupportUser();
        $this -> subsystemFilterForm = $subsystemFilterForm ?: new SubsystemFilterForm();
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
     * @return SubsystemCAdminStats|null
     */
    public function getSubsystemCAdminStats(): ?SubsystemCAdminStats
    {
        return $this -> subsystemCAdminStats;
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
    public function getSubsystemSupportUser(): ?SubsystemSupportUser
    {
        return $this -> subsystemSupportUser;
    }

    /**
     * @return SubsystemFilterForm|SubsystemTicket|null
     */
    public function getSubsystemFilterForm(): ?SubsystemFilterForm
    {
        return $this -> subsystemFilterForm;
    }
}

/**
 * Class SubsystemRole
 * @package admin\classes
 */
class SubsystemRole
{
    /**
     * @param false $returnValue
     * @return string[]
     */
    public function showAuthFormByRole($returnValue = false)
    {
        global $APPLICATION;
        $bDemo = (CTicket ::IsDemo()) ? "Y" : "N";
        $bAdmin = (CTicket ::IsAdmin()) ? "Y" : "N";
        $bSupportTeam = (CTicket ::IsSupportTeam()) ? "Y" : "N";
        if ($bAdmin != "Y" && $bSupportTeam != "Y" && $bDemo != "Y") {
            $APPLICATION -> AuthForm(GetMessage("ACCESS_DENIED"));
        }

        if ($returnValue) {
            return ["bAdmin" => $bAdmin, "bDemo" => $bDemo];
        }

        return null;
    }
}

/**
 * Class SubsystemGraph
 * @package admin\classes
 */
class SubsystemGraph
{
    /**
     * @var Graph|null
     */
    private $graph;

    /**
     * SubsystemGraph constructor.
     * @param Graph|null $graph
     */
    public function __construct(
        Graph $graph = null
    )
    {
        $this -> graph = $graph ?: new Graph();
    }

    /**
     * @param $ticket
     * @param $admin
     * @param $arrColorInc
     * @param $imageArFilter
     * @param null $width
     * @param null $height
     * @throws \Protobuf\Exception
     */
    public function createImage($ticket, $admin, $arrColorInc, $imageArFilter, $width = null, $height = null)
    {
        $this -> graph ->
        createImageGraph(
            $ticket -> getProperty('show_graph'),
            $admin -> getProperty('arFilterFields'),
            ['data' => $imageArFilter, 'emergency' => $admin -> getProperty('lAdmin') -> getFilter()],
            $arrColorInc ?? null,
            $width,
            $height
        );
    }

    /**
     * @return Graph|null
     */
    public function getGraph(): ?Graph
    {
        return $this -> graph;
    }

}

/**
 * Class SubsystemCAdminStats
 * @package admin\classes
 */
class SubsystemCAdminStats
{

    /**
     * @var CAdminStats|null
     */
    private $admin;

    /**
     * SubsystemCAdminStats constructor.
     * @param CAdminStats|null $admin
     */
    public function __construct(
        CAdminStats $admin = null
    )
    {
        $this -> admin = $admin ?: new CAdminStats();
    }

    /**
     * @param $sTableID
     * @return mixed|null
     */
    public function initCAdminPropertyList($sTableID)
    {
        $this -> admin -> addProperty('oSort', new CAdminSorting($sTableID));
        $this -> admin -> addProperty('lAdmin', new CAdminList($sTableID, $this -> admin -> getProperty('oSort')));
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
     * @return CAdminStats|null
     */
    public function getAdmin(): ?CAdminStats
    {
        return $this -> admin;
    }
}

/**
 * Class SubsystemTicket
 * @package admin\classes
 */
class SubsystemTicket
{

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
     * @return mixed|null
     * @return mixed|null
     */
    public function initTicketProperty($admin)
    {
        $this -> ticket -> addListTicketsDB("rsTickets", $admin -> getProperty('arFilter'));
        $this -> ticket -> addDefaultPropertyByKeys("arrTime", ["1", "1_2", "2_3", "3_4", "4_5", "5_6", "6_7", "7"], 0);
        $this -> ticket -> addDefaultPropertyByKeys("arrMess", ["2_m", "3_m", "4_m", "5_m", "6_m", "7_m", "8_m", "9_m", "10_m"], 0);
        $this -> ticket -> addAdditionalDataInto('rsTickets');

        return $this -> ticket -> getProperty('arTicketUsersID');
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
     */
    public function __construct(
        SupportUser $supportUser = null
    )
    {
        $this -> supportUser = $supportUser ?: new SupportUser();
    }

    /**
     * @param $arUsersID
     */
    public function addUsers($arUsersID)
    {
        $this -> supportUser -> setArrSupportUser($arUsersID);
        $this -> supportUser -> setSupportsUsersID($arUsersID);
        $this -> supportUser -> addSupportUsers();
    }

    /**
     * @return mixed
     */
    public function getArrSupportUser()
    {
        return $this -> supportUser -> getArrSupportUser();
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
    private function initFilterFormProperty($arrayProps)
    {
        foreach ($arrayProps as $key => $prop) {
            $this -> filterForm -> addProperty($key, $prop ?? null);
        }
    }

    /**
     * @param $arFilterFormProps
     */
    public function createAndShowFilterForm($arFilterFormProps)
    {
        $this -> initFilterFormProperty($arFilterFormProps);
        $this -> filterForm -> generateFilterForm();
    }
}
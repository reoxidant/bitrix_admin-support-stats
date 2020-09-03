<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CAdminException;
use CAdminMessage;

require_once('PropertyContainerInterface.php');

/**
 * Class CAdmin
 * @package admin\classes
 */
class CAdmin implements PropertyContainerInterface
{

    /**
     * @var array
     */
    private $propertyContainer = [];

    /**
     * @var
     */
    public $error;

    /**
     * @param false $returnValue
     * @return mixed|null
     */
    public function addArFilterFields($returnValue = false)
    {
        $this -> propertyContainer['arFilterFields'] = [
            "find_site_stats",
            "find_responsible_stats",
            "find_responsible_id_stats",
            "find_responsible_exact_match_stats",
            "find_category_id_stats",
            "find_criticality_id_stats",
            "find_status_id_stats",
            "find_sla_id_stats",
            "find_mark_id_stats",
            "find_source_id_stats",
            "find_date1_stats",
            "find_date2_stats",
            "find_work_in",
            "find_close_ticket",
            "find_wait_answer_dit",
            "find_wait_answer_user",
            "find_mess_stats",
            "find_overdue_mess_stats"
        ];

        if ($returnValue) {
            return $this -> getProperty("arFilterFields");
        }

        return null;
    }

    /** @noinspection PhpDeprecationInspection */
    public function checkFilter($find_date1, $find_date2)
    {
        reset($this -> propertyContainer['arFilterFields']);
        foreach ($this -> propertyContainer['arFilterFields'] as $f) global $$f;
        $arMsg = array();

        if (strlen(trim($find_date1)) > 0 || strlen(trim($find_date2)) > 0) {
            $date_1_ok = false;
            /** @noinspection PhpDeprecationInspection */
            $date1_stm = MkDateTime(ConvertDateTime($find_date1, "D.M.Y"), "d.m.Y");
            $date2_stm = MkDateTime(ConvertDateTime($find_date2, "D.M.Y") . " 23:59", "d.m.Y H:i");
            if (!$date1_stm && strlen(trim($find_date1)) > 0)
                $arMsg[] = array("id" => "find_date1", "text" => GetMessage("SUP_WRONG_DATE_FROM"));
            else
                $date_1_ok = true;

            if (!$date2_stm && strlen(trim($find_date2)) > 0)
                $arMsg[] = array("id" => "find_date2", "text" => GetMessage("SUP_WRONG_DATE_TILL"));

            elseif ($date_1_ok && $date2_stm <= $date1_stm && strlen($date2_stm) > 0)
                $arMsg[] = array("id" => "find_date2", "text" => GetMessage("SUP_FROM_TILL_DATE"));
        }

        if (!empty($arMsg)) {
            $e = new CAdminException($arMsg);
            $GLOBALS["APPLICATION"] -> ThrowException($e);
            return false;
        }

        return true;
    }

    /**
     * @param $data_filter
     */
    public function addArFilterData($data_filter)
    {
        global $APPLICATION;
        list(
            'find_site' => $find_site,
            'find_date1' => $find_date1,
            'find_date2' => $find_date2,
            'find_responsible_id' => $find_responsible_id,
            'find_responsible' => $find_responsible,
            'find_responsible_exact_match' => $find_responsible_exact_match,
            'find_sla_id' => $find_sla_id,
            'find_category_id' => $find_category_id,
            'find_criticality_id' => $find_criticality_id,
            'find_status_id' => $find_status_id,
            'find_mark_id' => $find_mark_id,
            'find_source_id' => $find_source_id
            ) = $data_filter;

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
            "SOURCE" => $find_source_id
        );

        if ($this -> checkFilter($find_date1, $find_date2)) {
            $this -> propertyContainer['arFilter'] = $arFilter;
        } else {
            if ($e = $APPLICATION -> GetException())
                $this -> error = new CAdminMessage(GetMessage("SUP_FILTER_ERROR"), $e);
        }
    }

    /**
     * @param string $sTableID
     * @return bool
     */
    public function IsDefaultFilter($sTableID = "t_report_graph")
    {
        $set_default = (!is_set($_REQUEST, "find_forum") ? (empty($_SESSION["SESS_STATS"]["LAST_TOPICS_LIST"]) ? "Y" : "N") : "N");;
        return $set_default == "Y" && (!isset($_SESSION["SESS_STATS"][$sTableID]) || empty($_SESSION["SESS_STATS"][$sTableID]));
    }

    /**
     * @param $arName
     * @param string $sTableID
     */
    public function initSessionFilter($arName, $sTableID = "t_report_graph")
    {
        $FILTER = $_SESSION["SESS_STATS"][$sTableID];

        foreach ($arName as $name) {
            global $$name;

            if (isset($$name))
                $FILTER[$name] = $$name;
            else
                $$name = $FILTER[$name];
        }

        $_SESSION["SESS_STATS"][$sTableID] = $FILTER;
    }

    /**
     * @param $arName
     * @param string $sTableID
     */
    public function DelFilter($arName, $sTableID = "t_report_graph")
    {
        unset($_SESSION["SESS_ADMIN"][$sTableID]);

        foreach ($arName as $name) {
            global $$name;
            $$name = "";
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function addProperty($name, $value)
    {
        $this -> propertyContainer[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getProperty($name)
    {
        return $this -> propertyContainer[$name] ?? null;
    }

}
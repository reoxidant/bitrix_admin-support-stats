<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

require_once('PropertyContainerInterface.php');

class CAdminFilter implements PropertyContainerInterface
{

    private $propertyContainer = [];

    public $error;

    public function setValDefaultFilter()
    {
        if ($this -> propertyContainer['lAdmin'] -> IsDefaultFilter()) {
            $this -> propertyContainer['defaultFilterValues'] = [
                'find_date1_DAYS_TO_BACK' => 1,
                'find_open' => "Y",
                'find_close' => "Y",
                "find_all" => "Y",
                'find_mess' => "Y",
                'find_overdue_mess' => "Y",
                'set_filter' => "Y"
            ];
        } else {
            $this -> propertyContainer['defaultFilterValues'] = null;
        }
    }

    public function setArFilterFields()
    {
        $this -> propertyContainer['arFilterFields'] = [
            "find_site",
            "find_responsible",
            "find_responsible_id",
            "find_responsible_exact_match",
            "find_category_id",
            "find_criticality_id",
            "find_status_id",
            "find_sla_id",
            "find_mark_id",
            "find_source_id",
            "find_date1",
            "find_date2",
            "find_open",
            "find_close",
            "find_all",
            "find_mess",
            "find_overdue_mess"
        ];
    }

    /** @noinspection PhpDeprecationInspection */
    public function checkFilter()
    {
        reset($this -> propertyContainer['arFilterFields']);
        foreach ($this -> propertyContainer['arFilterFields'] as $f) global $$f;
        $arMsg = array();

        if (strlen(trim($find_date1 ?? null)) > 0 || strlen(trim($find_date2 ?? null)) > 0) {
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

    public function addArrFilter()
    {
        $arFilter = array(
            "SITE" => $find_site ?? null,
            "DATE_CREATE_1" => $find_date1 ?? null,
            "DATE_CREATE_2" => $find_date2 ?? null,
            "RESPONSIBLE_ID" => $find_responsible_id ?? null,
            "RESPONSIBLE" => $find_responsible ?? null,
            "RESPONSIBLE_EXACT_MATCH" => $find_responsible_exact_match ?? null,
            "SLA" => $find_sla_id ?? null,
            "CATEGORY" => $find_category_id ?? null,
            "CRITICALITY" => $find_criticality_id ?? null,
            "STATUS" => $find_status_id ?? null,
            "MARK" => $find_mark_id ?? null,
            "SOURCE" => $find_source_id ?? null,
        );

        if ($this -> checkFilter()) {
            $this -> propertyContainer['arFilter'] = $arFilter;
        } else {
            if ($e = $APPLICATION -> GetException())
                $this->error = new CAdminMessage(GetMessage("SUP_FILTER_ERROR"), $e);
        }
    }

    public function addProperty($name, $value)
    {
        $this -> propertyContainer[$name] = $value;
    }

    public function getProperty($name)
    {
        return $this -> propertyContainer[$name] ?? null;
    }

    public function getAllProperties()
    {
        return $this -> propertyContainer ?? null;
    }
}
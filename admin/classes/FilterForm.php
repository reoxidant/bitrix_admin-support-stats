<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CTicketDictionary;

/**
 * Class FilterForm
 * @package admin\classes
 */
class FilterForm implements PropertyContainerInterface
{
    /**
     * @var array
     */
    private $propertyContainer = [];

    /**
     * @param $find_date1
     * @param $find_date2
     * @return string
     */
    private function createCalendarPeriod($find_date1, $find_date2)
    {
        return CalendarPeriod(
            "find_date1_stats",
            $find_date1,
            "find_date2_stats",
            $find_date2,
            "form1",
            "Y");
    }

    private function initClassAdminFilter(){
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

        return new CAdminFilterStats("stats_filter_id", $arrMessages);
    }

    /**
     *
     */
    public function generateFilterForm()
    {
        $filter = $this->initClassAdminFilter();

        global $APPLICATION;
        ?>
        <form name="form1_stats" method="GET" action="<?= $APPLICATION -> GetCurPage() ?>?">
            <? $filter->Begin(); ?>
            <tr>
                <td><? echo GetMessage("SUP_F_PERIOD") . "(" . FORMAT_DATE . "):" ?></td>
                <td><? echo $this -> createCalendarPeriod($this -> getProperty("find_date1"), $this -> getProperty("find_date2")) ?></td>
            </tr>
            <tr>
                <td nowrap>
                    <?=GetMessage("SUP_F_STATUS")?>:
                </td>
                <td>
                    <?
                    $ref = array(); $ref_id = array();
                    $ref[] = GetMessage("SUP_NO"); $ref_id[] = "0";
                    $z = CTicketDictionary::GetDropDown("S");
                    while ($zr = $z->Fetch())
                    {
                        $ref[] = $zr["REFERENCE"];
                        $ref_id[] = $zr["REFERENCE_ID"];
                    }
                    $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
                    echo SelectBoxFromArray("find_status_id_stats", $arr, $this -> getProperty("find_status_id"), GetMessage("SUP_ALL"));
                    ?>
                </td>
            </tr>
            <? $filter ->
            Buttons(array(
                "table_id" => $this -> getProperty("sTableID"),
                "url" => $APPLICATION -> GetCurPage(),
                "form" => "form1")
            );
            $filter->End();?>
        </form>
        <?php
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

?>
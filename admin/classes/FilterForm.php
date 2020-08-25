<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CSite;
use CTicket;
use CTicketSLA;

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
            "find_date1", $find_date1,
            "find_date2", $find_date2,
            "form1", "Y");
    }

    /**
     * @param $find_site
     * @return string
     */
    private function createSiteBox($find_site)
    {
        $ref = array();
        $ref_id = array();
        $sort = "sort";
        $typeSort = "asc";
        $rs = CSite ::GetList($sort, $typeSort);
        while ($ar = $rs -> Fetch()) {
            $ref[] = "[" . $ar["ID"] . "] " . $ar["NAME"];
            $ref_id[] = $ar["ID"];
        }
        return SelectBoxMFromArray(
            "find_site[]",
            array("reference" => $ref, "reference_id" => $ref_id),
            $find_site,
            "",
            false,
            "3"
        );
    }

    /**
     * @param $arrSupportUser
     * @param $find_responsible_id
     * @return string
     */
    private function getSelectBoxSupportTeam($arrSupportUser, $find_responsible_id)
    {
        $ref = array();
        $ref_id = array();
        $ref[] = GetMessage("SUP_NO");
        $ref_id[] = "0";
        $z = CTicket ::GetSupportTeamList();
        while ($zr = $z -> Fetch()) {
            $ref[] = $zr["REFERENCE"];
            $ref_id[] = $zr["REFERENCE_ID"];
        }
        if (is_array($arrSupportUser) && count($arrSupportUser) > 0) {
            ksort($arrSupportUser);
            while (list($key, $arUser) = each($arrSupportUser)) {
                if (!in_array($key, $ref_id)) {
                    $ref[] = $arUser["LAST_NAME"] . " " . $arUser["NAME"] . " (" . $arUser["LOGIN"] . ") " . "[" . $key . "]";
                    $ref_id[] = $key;
                }
            }
        }
        $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
        return SelectBoxFromArray("find_responsible_id", $arr, htmlspecialcharsbx($find_responsible_id), GetMessage("SUP_ALL"));
    }

    /**
     * @param $bAdmin
     * @param $bDemo
     * @param $arrSupportUser
     * @param $find_responsible
     * @param $find_responsible_id
     * @param $find_responsible_exact_match
     */
    private function createResponsibleBox($bAdmin, $bDemo, $arrSupportUser, $find_responsible, $find_responsible_id, $find_responsible_exact_match)
    {
        if ($bAdmin == "Y" || $bDemo == "Y"):?>
            <?= $this -> getSelectBoxSupportTeam($arrSupportUser, $find_responsible_id) ?>
            <br>
            <input class="typeinput" type="text" name="find_responsible" size="47"
                   value="<?= htmlspecialcharsbx($find_responsible) ?>">
            <?=
            InputType(
                "checkbox",
                "find_responsible_exact_match",
                "Y",
                $find_responsible_exact_match,
                false,
                "",
                "title='" . GetMessage("SUP_EXACT_MATCH") . "'
                ")
            ?>
            &nbsp;
            <?= ShowFilterLogicHelp() ?>
        <? else : ?>
            [
            <a href="/bitrix/admin/user_edit.php?ID=<?= $USER -> GetID() ?>"><?= $USER -> GetID() ?></a>](<?= htmlspecialcharsEx($USER -> GetLogin()) ?>)
            <?= htmlspecialcharsEx($USER -> GetFullName()) ?>
        <?endif;
    }

    /**
     * @param $find_name_id
     * @param $find_id
     */
    private function createDropDownItem($find_name_id, $find_id)
    {
        $ref = array();
        $ref_id = array();
        $ref[] = GetMessage("SUP_NO");
        $ref_id[] = "0";
        $z = (new CTicketSLA) -> GetDropDown();
        while ($zr = $z -> Fetch()) {
            $ref[] = $zr["REFERENCE"];
            $ref_id[] = $zr["REFERENCE_ID"];
        }
        $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
        echo SelectBoxFromArray($find_name_id, $arr, $find_id, GetMessage("SUP_ALL"));

    }

    /**
     * @param $arDropDown
     */
    private function createDropDownList($arDropDown)
    {
        foreach ($arDropDown as $nameItemDropDown => $itemDropDown) {
            ?>
            <tr>
                <td nowrap><?= GetMessage($itemDropDown['message']) ?>:</td>
                <td><? $this -> createDropDownItem($nameItemDropDown, $itemDropDown['data']) ?></td>
            </tr>
        <? }
    }

    /**
     * @param $arCheckBoxData
     */
    private function createCheckBoxList($arCheckBoxData)
    {
        foreach ($arCheckBoxData as $nameCheckBox => $itemCheckBox) {
            ?>
            <tr>
                <td nowrap><?= GetMessage($itemCheckBox['message']) ?></td>
                <td align="center"><? echo InputType("checkbox", $nameCheckBox, "Y", $itemCheckBox['data'], false); ?></td>
            </tr>
        <? }
    }

    //TODO: check working the filter form

    /**
     *
     */
    public function generateFilterForm()
    {
        global $APPLICATION;
        ?>
        <form name="form1" method="GET" action="<?= $APPLICATION -> GetCurPage() ?>?">
            <? $this -> getProperty('filter') -> Begin(); ?>
            <tr>
                <td><?/* echo GetMessage("SUP_F_PERIOD")."(".FORMAT_DATE."):"*/ ?></td>
                <td><?/* echo $this->createCalendarPeriod($this->getProperty("find_date1"), $this->getProperty("find_date2")) */ ?></td>
            </tr>
            <tr valign="top">
                <td valign="top"><?/*= GetMessage("SUP_F_SITE") */ ?>:</td>
                <td><?/*= $this->createSiteBox( $this->getProperty("find_site"));*/ ?></td>
            </tr>
            <tr>
                <td nowrap valign="top"><?/*= GetMessage("SUP_F_RESPONSIBLE") */ ?>:</td>
                <td><?/*
                    $this->createResponsibleBox(
                        $this->getProperty("bAdmin"),
                        $this->getProperty("bDemo"),
                        $this->getProperty("arrSupportUser"),
                        $this->getProperty("find_responsible"),
                        $this->getProperty("find_responsible_id"),
                        $this->getProperty("find_responsible_exact_match")
                   ) */ ?></td>
            </tr>
            <?php
            /*                $dropDownData = [
                                'find_sla_id' => ['message' => "SUP_F_SLA", "data" => $this->getProperty("find_sla_id")],
                                'find_category_id' => ['message' => "SUP_F_CATEGORY", "data" => $this->getProperty("find_category_id")],
                                'find_criticality_id'=> ['message' => "SUP_F_CRITICALITY", "data" => $this->getProperty("find_criticality_id")],
                                'find_status_id'=> ['message' => "SUP_F_STATUS", "data" => $this->getProperty("find_status_id")],
                                'find_mark_id'=> ['message' => "SUP_F_MARK", "data" => $this->getProperty("find_mark_id")],
                                'find_source_id'=> ['message' => "SUP_F_SOURCE", "data" => $this->getProperty("find_source_id")]
                            ];

                            $this->createDropDownList($dropDownData)
                        */ ?>
            <tr valign="top">
                <td width="0%" nowrap><?/*= GetMessage("SUP_SHOW") */ ?>:</td>
                <td width="0%" nowrap valign="top">
                    <table border="0" cellspacing="2" cellpadding="0" width="0%" style="margin-left: 12px">
                        <tr>
                            <td valign="top" align="center">
                                <table border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td valign="top">
                                            <table cellpadding="3" cellspacing="1" border="0">
                                                <?/*
                                                    $checkBoxData = [
                                                          "find_open" => ['message' => "SUP_OPEN_TICKET", 'data' => $this->getProperty("find_open")],
                                                          "find_close" => ['message' => "SUP_CLOSE_TICKET", 'data' => $this->getProperty("find_close")],
                                                          "find_all" => ['message' => "SUP_ALL_TICKET", 'data' =>  $this->getProperty("find_all")],
                                                          "find_mess" => ['message' => "SUP_MESSAGES", 'data' => $this->getProperty("find_mess")],
                                                          "find_overdue_mess" => ['message' => "SUP_OVERDUE_MESSAGES", 'data' => $this->getProperty("find_overdue_mess")],
                                                    ];

                                                    $this->createCheckBoxList($checkBoxData);
                                                */ ?>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <? $this -> getProperty("filter") ->
            Buttons(array(
                    "table_id" => $this -> getProperty("sTableID"),
                    "url" => $APPLICATION -> GetCurPage(),
                    "form" => "form1")
            );
            $this -> getProperty("filter") -> End(); ?>
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
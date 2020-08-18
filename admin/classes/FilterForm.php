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
use CTicketDictionary;
use CTicketSLA;

class FilterForm
{

    private function createCalendarPeriod($find_date1, $find_date2){
        return CalendarPeriod(
            "find_date1", $find_date1,
            "find_date2", $find_date2,
            "form1", "Y");
    }

    private function createSiteBox($find_site){
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

    private function getSelectBoxSupportTeam($arrSupportUser, $find_responsible_id){
        $ref = array(); $ref_id = array();
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

    private function createResponsibleBox($bAdmin, $bDemo, $arrSupportUser, $find_responsible, $find_responsible_id, $find_responsible_exact_match){
        if ($bAdmin == "Y" || $bDemo == "Y"):?>
            <?= $this->getSelectBoxSupportTeam($arrSupportUser, $find_responsible_id)?>
        <br>
        <input class="typeinput" type="text" name="find_responsible" size="47" value="<?= htmlspecialcharsbx($find_responsible) ?>">
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
        [<a href="/bitrix/admin/user_edit.php?ID=<?= $USER -> GetID() ?>"><?= $USER -> GetID() ?></a>](<?= htmlspecialcharsEx($USER -> GetLogin()) ?>)
        <?= htmlspecialcharsEx($USER -> GetFullName()) ?>
    <?endif;
    }

    private function createDropDown($find_name_id, $find_id){
        $ref = array();
        $ref_id = array();
        $ref[] = GetMessage("SUP_NO");
        $ref_id[] = "0";
        $z = CTicketSLA ::GetDropDown();
        while ($zr = $z -> Fetch()) {
            $ref[] = $zr["REFERENCE"];
            $ref_id[] = $zr["REFERENCE_ID"];
        }
        $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
        echo SelectBoxFromArray($find_name_id, $arr, $find_id, GetMessage("SUP_ALL"));

    }

    public function createFilterForm(
            $filter,
            $find_site,
            $find_date1,
            $find_date2,
            $bAdmin,
            $bDemo,
            $arrSupportUser,
            $find_responsible,
            $find_responsible_id,
            $find_responsible_exact_match,
            $find_sla_id,
            $find_category_id,
            $find_criticality_id
    ){?>
        <form name="form1" method="GET" action="<?= $APPLICATION -> GetCurPage() ?>?">
            <? $filter -> Begin(); ?>
            <tr>
                <td><? echo GetMessage("SUP_F_PERIOD")."(".FORMAT_DATE."):"?></td>
                <td><? echo $this->createCalendarPeriod($find_date1, $find_date2) ?></td>
            </tr>
            <tr valign="top">
                <td valign="top"><?= GetMessage("SUP_F_SITE") ?>:</td>
                <td><? echo $this->createSiteBox($find_site);?></td>
            </tr>
            <tr>
                <td nowrap valign="top"><?= GetMessage("SUP_F_RESPONSIBLE") ?>:</td>
                <td><? $this->createResponsibleBox($bAdmin, $bDemo, $arrSupportUser, $find_responsible, $find_responsible_id, $find_responsible_exact_match) ?></td>
            </tr>
            <tr>
                <td nowrap><?= GetMessage("SUP_F_SLA") ?>:</td>
                <td><?= $this->createDropDown("find_sla_id", $find_sla_id)?></td>
            </tr>
            <tr>
                <td nowrap><?= GetMessage("SUP_F_CATEGORY") ?>:</td>
                <td>
                    <? $this->createDropDown('find_category_id', $find_category_id) ?>
                </td>
            </tr>
            <tr>
                <td nowrap>
                    <?= GetMessage("SUP_F_CRITICALITY") ?>:
                </td>
                <td><? $this -> createDropDown('find_criticality_id', $find_criticality_id) ?></td>
            </tr>
            <tr>
                <td nowrap>
                    <?= GetMessage("SUP_F_STATUS") ?>:
                </td>
                <td><?
                    $ref = array();
                    $ref_id = array();
                    $ref[] = GetMessage("SUP_NO");
                    $ref_id[] = "0";
                    $z = CTicketDictionary ::GetDropDown("S");
                    while ($zr = $z -> Fetch()) {
                        $ref[] = $zr["REFERENCE"];
                        $ref_id[] = $zr["REFERENCE_ID"];
                    }
                    $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
                    echo SelectBoxFromArray("find_status_id", $arr, $find_status_id, GetMessage("SUP_ALL"));
                    ?></td>
            </tr>
            <tr>
                <td nowrap>
                    <?= GetMessage("SUP_F_MARK") ?>:
                </td>
                <td><?
                    $ref = array();
                    $ref_id = array();
                    $ref[] = GetMessage("SUP_NO");
                    $ref_id[] = "0";
                    $z = CTicketDictionary ::GetDropDown("M");
                    while ($zr = $z -> Fetch()) {
                        $ref[] = $zr["REFERENCE"];
                        $ref_id[] = $zr["REFERENCE_ID"];
                    }
                    $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
                    echo SelectBoxFromArray("find_mark_id", $arr, $find_mark_id, GetMessage("SUP_ALL"));
                    ?></td>
            </tr>
            <tr>
                <td nowrap>
                    <?= GetMessage("SUP_F_SOURCE") ?>:
                </td>
                <td><?
                    $ref = array();
                    $ref_id = array();
                    $ref[] = "web";
                    $ref_id[] = "0";
                    $z = CTicketDictionary ::GetDropDown("SR");
                    while ($zr = $z -> Fetch()) {
                        $ref[] = "[" . $zr["ID"] . "] (" . $zr["SID"] . ") " . $zr["NAME"];
                        $ref_id[] = $zr["REFERENCE_ID"];
                    }
                    $arr = array("REFERENCE" => $ref, "REFERENCE_ID" => $ref_id);
                    echo SelectBoxFromArray("find_source_id", $arr, $find_source_id, GetMessage("SUP_ALL"));
                    ?></td>
            </tr>
            <tr valign="top">
                <td width="0%" nowrap><?= GetMessage("SUP_SHOW") ?>:</td>
                <td width="0%" nowrap valign="top">
                    <table border="0" cellspacing="2" cellpadding="0" width="0%" style="margin-left: 12px">
                        <tr>
                            <td valign="top" align="center">
                                <table border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td valign="top">
                                            <table cellpadding="3" cellspacing="1" border="0">
                                                <tr>
                                                    <td nowrap><?= GetMessage("SUP_OPEN_TICKET") ?></td>
                                                    <td align="center"><? echo InputType("checkbox", "find_open", "Y", $find_open, false); ?></td>
                                                </tr>
                                                <tr>
                                                    <td nowrap><?= GetMessage("SUP_CLOSE_TICKET") ?></td>
                                                    <td align="center"><? echo InputType("checkbox", "find_close", "Y", $find_close, false); ?></td>
                                                </tr>
                                                <tr>
                                                    <td nowrap><?= GetMessage("SUP_ALL_TICKET") ?></td>
                                                    <td align="center"><? echo InputType("checkbox", "find_all", "Y", $find_all, false); ?></td>
                                                </tr>
                                                <tr>
                                                    <td nowrap><?= GetMessage("SUP_MESSAGES") ?></td>
                                                    <td align="center"><? echo InputType("checkbox", "find_mess", "Y", $find_mess, false); ?></td>
                                                </tr>
                                                <tr>
                                                    <td nowrap><?= GetMessage("SUP_OVERDUE_MESSAGES") ?></td>
                                                    <td align="center"><? echo InputType("checkbox", "find_overdue_mess", "Y", $find_overdue_mess, false); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <? $filter -> Buttons(array("table_id" => $sTableID, "url" => $APPLICATION -> GetCurPage(), "form" => "form1")); $filter -> End(); ?>
        </form>
<?php
    }
}
?>
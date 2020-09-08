<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CHotKeys;
use CHTTP;
use CMain;
use COption;
use CUserOptions;
use CUtil;

/**
 * Class CAdminFilterStats
 * @package admin\classes
 */
class CAdminFilterStats
{
    /**
     * @var
     */
    public $id;
    /**
     * @var array|false
     */
    private $popup;
    /**
     * @var array
     */
    private $arItems = array();
    /**
     * @var false|mixed
     */
    private $arOptFlt = array();
    /**
     * @var int
     */
    private static $defaultSort = 100;
    /**
     * @var bool|mixed
     */
    private $url = false;
    /**
     * @var bool|mixed
     */
    private $tableId = false;

    /**
     *
     */
    const SESS_PARAMS_NAME = "main.adminFilter";

    /**
     * CAdminFilterStats constructor.
     * @param $id
     * @param false $popup
     * @param array $arExtraParams
     */
    public function __construct($id, $popup = false, $arExtraParams = array())
    {
        global $USER;

        $uid = $USER -> GetID(); // for getList
        //$isAdmin = $USER -> CanDoOperation('edit_other_settings'); //Get Edit or not

        if (empty($popup) || !is_array($popup))
            $popup = false;

        $this -> id = $id;
        $this -> popup = $popup;

        if (is_array($arExtraParams)) {
            if (isset($arExtraParams["url"]) && !empty($arExtraParams["url"]))
                $this -> url = $arExtraParams["url"];

            if (isset($arExtraParams["table_id"]) && !empty($arExtraParams["table_id"]))
                $this -> tableId = $arExtraParams["table_id"];
        }

        if ($this -> id == "stats_filter_id") {
            $this -> arOptFlt["rows"] = implode(",", [0, 1, 2, 3, 4, 5, 6, 7, 8, "miss-1", "miss-0"]);
        }

        $presetsDeleted = explode(",", $this -> arOptFlt["presetsDeleted"]);

        $this -> arOptFlt["presetsDeleted"] = $presetsDeleted ? $presetsDeleted : array();

        $presetsDeletedJS = '';

        if (is_array($presetsDeleted))
            foreach ($presetsDeleted as $preset)
                if (trim($preset) <> "")
                    $presetsDeletedJS .= ($presetsDeletedJS <> "" ? "," : "") . '"' . CUtil ::JSEscape(trim($preset)) . '"';

        $this -> arOptFlt["presetsDeletedJS"] = $presetsDeletedJS;

        $dbRes = self ::GetList(array(), array("USER_ID" => $uid, "FILTER_ID" => $this -> id), true);
        while ($arFilter = $dbRes -> Fetch()) {
            if (!is_null($arFilter["LANGUAGE_ID"]) && $arFilter["LANGUAGE_ID"] != LANG)
                continue;

            $arItem = $arFilter;
            $arItem["FIELDS"] = unserialize($arFilter["FIELDS"]);

            if (!is_null($arFilter["SORT_FIELD"]))
                $arItem["SORT_FIELD"] = unserialize($arFilter["SORT_FIELD"]);

            if ($arFilter["PRESET"] == "Y" && is_null($arFilter["LANGUAGE_ID"])) {
                $langName = GetMessage($arFilter["NAME"]);

                if ($langName)
                    $arItem["NAME"] = $langName;

                foreach ($arItem["FIELDS"] as $key => $field) {
                    $langValue = GetMessage($arItem["FIELDS"][$key]["value"]);

                    if ($langValue)
                        $arItem["FIELDS"][$key]["value"] = $langValue;
                }
            }

//            $arItem["EDITABLE"] = ((($isAdmin || $arFilter["USER_ID"] == $uid) && $arFilter["PRESET"] != "Y") ? true : false);

            $this -> AddItem($arItem);
        }
    }

    /**
     * @return string
     */
    private function err_mess()
    {
        return "<br>Class: CAdminFilterStats<br>File: " . __FILE__;
    }

    /**
     * @param $arItem
     * @param false $bInsertFirst
     * @return bool
     */
    private function AddItem($arItem, $bInsertFirst = false)
    {
        //if user "deleted" preset http://jabber.bx/view.php?id=34405
        if (!$arItem["EDITABLE"] && !empty($this -> arOptFlt["presetsDeleted"]))
            if (in_array($arItem["ID"], $this -> arOptFlt["presetsDeleted"]))
                return false;

        $customPresetId = $this -> FindItemByPresetId($arItem["ID"]);

        if ($customPresetId) {
            $this -> arItems[$customPresetId]["SORT"] = $arItem["SORT"];
            return false;
        }

        if (isset($arItem["PRESET_ID"])) {
            $presetID = $this -> FindItemByID($arItem["PRESET_ID"]);

            if ($presetID) {
                $arItem["SORT"] = $this -> arItems[$presetID]["SORT"];
                unset($this -> arItems[$presetID]);
            }

        }

        if (!isset($arItem["SORT"]))
            $arItem["SORT"] = self ::$defaultSort;

        if ($bInsertFirst) {
            $arNewItems[$arItem["ID"]] = $arItem;

            foreach ($this -> arItems as $key => $item)
                $arNewItems[$key] = $item;

            $this -> arItems = $arNewItems;
        } else
            $this -> arItems[$arItem["ID"]] = $arItem;

        unset($this -> arItems[$arItem["ID"]][$arItem["ID"]]);

        return true;
    }

    /**
     * @param $arFields
     * @return false
     */
    public static function Add($arFields)
    {
        global $DB;

        $arFields["FIELDS"] = self ::FieldsDelHiddenEmpty($arFields["FIELDS"]);

        if (!$arFields["FIELDS"])
            return false;

        $arFields["FIELDS"] = serialize($arFields["FIELDS"]);

        if (isset($arFields["SORT_FIELD"]))
            $arFields["SORT_FIELD"] = serialize($arFields["SORT_FIELD"]);

        if (!self ::CheckFields($arFields))
            return false;

        $ID = $DB -> Add("b_filters", $arFields, array("FIELDS"));
        return $ID;
    }

    /**
     * @param array $aSort
     * @param array $arFilter
     * @param bool $getCommon
     * @return \CDBResult|false
     */
    public static function GetList($aSort = array(), $arFilter = array(), $getCommon = true)
    {
        global $DB;

        $err_mess = (self ::err_mess()) . "<br>Function: GetList<br>Line: ";
        $arSqlSearch = array();
        if (is_array($arFilter)) {
            foreach ($arFilter as $key => $val) {
                if (strlen($val) <= 0 || $val == "NOT_REF")
                    continue;

                switch (strtoupper($key)) {
                    case "ID":
                        $arSqlSearch[] = GetFilterQuery("F.ID", $val, "N");
                        break;
                    case "USER_ID":
                        if ($getCommon)
                            $arSqlSearch[] = "F.USER_ID=" . intval($val) . " OR F.COMMON='Y'";
                        else
                            $arSqlSearch[] = "F.USER_ID = " . intval($val);
                        break;
                    case "FILTER_ID":
                        $arSqlSearch[] = "F.FILTER_ID = '" . $DB -> ForSql($val) . "'";
                        break;
                    case "NAME":
                        $arSqlSearch[] = GetFilterQuery("F.NAME", $val);
                        break;
                    case "FIELDS":
                        $arSqlSearch[] = GetFilterQuery("F.FIELDS", $val);
                        break;
                    case "COMMON":
                        $arSqlSearch[] = "F.COMMON = '" . $DB -> ForSql($val, 1) . "'";
                        break;
                    case "PRESET":
                        $arSqlSearch[] = "F.PRESET = '" . $DB -> ForSql($val, 1) . "'";
                        break;
                    case "LANGUAGE_ID":
                        $arSqlSearch[] = "F.LANGUAGE_ID = '" . $DB -> ForSql($val, 2) . "'";
                        break;
                    case "PRESET_ID":
                        $arSqlSearch[] = GetFilterQuery("F.PRESET_ID", $val);
                        break;
                    case "SORT":
                        $arSqlSearch[] = GetFilterQuery("F.SORT", $val);
                        break;
                    case "SORT_FIELD":
                        $arSqlSearch[] = GetFilterQuery("F.SORT_FIELD", $val);
                        break;
                }
            }
        }

        $sOrder = "";
        foreach ($aSort as $key => $val) {
            $ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
            switch (strtoupper($key)) {
                case "ID":
                    $sOrder .= ", F.ID " . $ord;
                    break;
                case "USER_ID":
                    $sOrder .= ", F.USER_ID " . $ord;
                    break;
                case "FILTER_ID":
                    $sOrder .= ", F.FILTER_ID " . $ord;
                    break;
                case "NAME":
                    $sOrder .= ", F.NAME " . $ord;
                    break;
                case "FIELDS":
                    $sOrder .= ", F.FIELDS " . $ord;
                    break;
                case "COMMON":
                    $sOrder .= ", F.COMMON " . $ord;
                    break;
                case "PRESET":
                    $sOrder .= ", F.PRESET " . $ord;
                    break;
                case "LANGUAGE_ID":
                    $sOrder .= ", F.LANGUAGE_ID " . $ord;
                    break;
                case "PRESET_ID":
                    $sOrder .= ", F.PRESET_ID " . $ord;
                    break;
                case "SORT":
                    $sOrder .= ", F.SORT " . $ord;
                    break;
                case "SORT_FIELD":
                    $sOrder .= ", F.SORT_FIELD " . $ord;
                    break;
            }
        }
        if (strlen($sOrder) <= 0)
            $sOrder = "F.ID ASC";
        $strSqlOrder = " ORDER BY " . TrimEx($sOrder, ",");

        $strSqlSearch = GetFilterSqlSearch($arSqlSearch, "noFilterLogic");
        $strSql = "
			SELECT
				F.ID, F.USER_ID, F.NAME, F.FILTER_ID, F.FIELDS, F.COMMON, F.PRESET, F.LANGUAGE_ID, F.PRESET_ID, F.SORT, F.SORT_FIELD
			FROM
				b_filters F
			WHERE
			" . $strSqlSearch . "
			" . $strSqlOrder;

        $res = $DB -> Query($strSql, false, $err_mess . __LINE__);
        return $res;
    }

    /**
     *
     */
    public function Begin()
    {
        uasort($this -> arItems, "self::Cmp");

        echo '
<div id="adm-filter-tab-wrap-' . $this -> id . '" class="adm-filter-wrap' . ($this -> arOptFlt["styleFolded"] == "Y" ? " adm-filter-folded" : "") . '" style = "display: none;">
	<table class="adm-filter-main-table">
		<tr>
			<td class="adm-filter-main-table-cell">
				<div class="adm-filter-tabs-block" id="filter-tabs-' . $this -> id . '">
					<span id="adm-filter-tab-' . $this -> id . '-0" class="adm-filter-tab adm-filter-tab-active" onclick="' . $this -> id . '.SetActiveTab(this); ' . $this -> id . '.ApplyFilter(\'0\'); " title="' . GetMessage("admin_lib_filter_goto_dfilter") . '">' . GetMessage("admin_lib_filter_filter") . '</span>';

        if (is_array($this -> arItems) && !empty($this -> arItems)) {
            foreach ($this -> arItems as $filter_id => $filter) {
                $name = ($filter["NAME"] <> '' ? $filter["NAME"] : GetMessage("admin_lib_filter_no_name"));
                echo '<span id="adm-filter-tab-' . $this -> id . '-' . $filter_id . '" class="adm-filter-tab" onclick="' . $this -> id . '.SetActiveTab(this); ' . $this -> id . '.ApplyFilter(\'' . $filter_id . '\');" title="' . GetMessage("admin_lib_filter_goto_filter") . ": &quot;" . htmlspecialcharsbx($name) . '&quot;">' . htmlspecialcharsbx($name) . '</span>';
            }
        }

        echo '<span id="adm-filter-add-tab-' . $this -> id . '" class="adm-filter-tab adm-filter-add-tab" onclick="' . $this -> id . '.SaveAs();" title="' . GetMessage("admin_lib_filter_new") . '"></span><span onclick="' . $this -> id . '.SetFoldedView();" class="adm-filter-switcher-tab"><span id="adm-filter-switcher-tab" class="adm-filter-switcher-tab-icon"></span></span><span class="adm-filter-tabs-block-underlay"></span>
				</div>
			</td>
		</tr>
		<tr>
			<td class="adm-filter-main-table-cell">
				<div class="adm-filter-content" id="' . $this -> id . '_content">
					<div class="adm-filter-content-table-wrap">
						<table cellspacing="0" class="adm-filter-content-table" id="' . $this -> id . '">';
    }

    /**
     * @param bool|array $aParams
     */
    public function Buttons($aParams = false)
    {
        $hkInst = CHotKeys ::getInstance();

        echo '

						</table>
					</div>
					<div class="adm-filter-bottom-separate" id="' . $this -> id . '_bottom_separator"></div>
					<div class="adm-filter-bottom">';

        if ($aParams !== false) {
            $url = $aParams["url"];
            if (strpos($url, "?") === false)
                $url .= "?";
            else
                $url .= "&";

            if (strpos($url, "lang=") === false)
                $url .= "lang=" . LANG;

            if (!$this -> url)
                $this -> url = $url;

            if (!$this -> tableId)
                $this -> tableId = $aParams["table_id"];

            if (isset($aParams['report']) && $aParams['report']) {
                echo '
						<input type="submit" class="adm-btn" id="' . $this -> id . 'set_filter" name="set_filter" title="' . GetMessage("admin_lib_filter_set_rep_title") . $hkInst -> GetTitle("set_filter") . '" onclick="return ' . htmlspecialcharsbx($this -> id . '.OnSet(\'' . CUtil ::AddSlashes($aParams["table_id"]) . '\', \'' . CUtil ::AddSlashes($url) . '\', this);') . '" value="' . GetMessage("admin_lib_filter_set_rep") . '">
						<input type="submit" class="adm-btn" id="' . $this -> id . 'del_filter" name="del_filter" title="' . GetMessage("admin_lib_filter_clear_butt_title") . $hkInst -> GetTitle("del_filter") . '" onclick="return ' . htmlspecialcharsbx($this -> id . '.OnClear(\'' . CUtil ::AddSlashes($aParams["table_id"]) . '\', \'' . CUtil ::AddSlashes($url) . '\', this);') . '" value="' . GetMessage("admin_lib_filter_clear_butt") . '">';
            } else
                echo '
						<input type="submit" class="adm-btn" id="' . $this -> id . 'set_filter" name="set_filter" title="' . GetMessage("admin_lib_filter_set_butt") . $hkInst -> GetTitle("set_filter") . '" onclick="return ' . htmlspecialcharsbx($this -> id . '.OnSet(\'' . CUtil ::AddSlashes($aParams["table_id"]) . '\', \'' . CUtil ::AddSlashes($url) . '\', this);') . '" value="' . GetMessage("admin_lib_filter_set_butt") . '">
						<input type="submit" class="adm-btn" id="' . $this -> id . 'del_filter" name="del_filter" title="' . GetMessage("admin_lib_filter_clear_butt") . $hkInst -> GetTitle("del_filter") . '" onclick="return ' . htmlspecialcharsbx($this -> id . '.OnClear(\'' . CUtil ::AddSlashes($aParams["table_id"]) . '\', \'' . CUtil ::AddSlashes($url) . '\', this);') . '" value="' . GetMessage("admin_lib_filter_clear_butt") . '">';

        }
        if ($this -> popup) {

            echo '
						<div class="adm-filter-setting-block">
							<span class="adm-filter-setting" onClick="this.blur();' . $this -> id . '.SaveMenuShow(this);return false;" hidefocus="true" title="' . GetMessage("admin_lib_filter_savedel_title") . '"></span>
							<span class="adm-filter-add-button" onClick="this.blur();' . $this -> id . '.SettMenuShow(this);return false;" hidefocus="true" title="' . GetMessage("admin_lib_filter_more_title") . '"></span>
						</div>';
        }
    }

    /**
     *
     */
    public function End()
    {

        echo '
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>';

        $sRowIds = $sVisRowsIds = "";


        if (is_array($this -> popup)) {
            foreach ($this -> popup as $key => $item)
                if ($item !== null)
                    $sRowIds .= ($sRowIds <> "" ? "," : "") . '"' . CUtil ::JSEscape($key) . '"';
            //TODO: set row as default value if not params and see how it is do on admin bitrix framework
            $aRows = explode(",", $this -> arOptFlt["rows"]);

            if (is_array($aRows))
                foreach ($aRows as $row)
                    if (trim($row) <> "")
                        $sVisRowsIds .= ($sVisRowsIds <> "" ? "," : "") . '"' . CUtil ::JSEscape(trim($row)) . '":true';
        }

        $openedTabUri = false;
        $openedTabSes = $filteredTab = null;

        if (isset($_REQUEST["adm_filter_applied"]) && !empty($_REQUEST["adm_filter_applied"])) {
            $openedTabUri = $_REQUEST["adm_filter_applied"];
        } else {
            $openedTabSes = $_SESSION[self::SESS_PARAMS_NAME][$this -> id]["activeTabId"];
            $filteredTab = $_SESSION[self::SESS_PARAMS_NAME][$this -> id]["filteredId"];
        }

        echo '
<script type="text/javascript">
	var ' . $this -> id . ' = {};
	BX.ready(function(){
		' . $this -> id . ' = new BX.AdminFilter("' . $this -> id . '", [' . $sRowIds . ']);
		if (!BX.adminMenu)
		{
			BX.adminMenu = new BX.adminMenu();
		}
		' . $this -> id . '.state.init = true;
		' . $this -> id . '.state.folded = ' . ($this -> arOptFlt["styleFolded"] == "Y" ? "true" : "false") . ';
		' . $this -> id . '.InitFilter({' . $sVisRowsIds . '});
		' . $this -> id . '.oOptions = ' . CUtil ::PhpToJsObject($this -> arItems) . ';
		' . $this -> id . '.popupItems = ' . CUtil ::PhpToJsObject($this -> popup) . ';
		' . $this -> id . '.InitFirst();
		' . $this -> id . '.url = "' . CUtil ::JSEscape($this -> url) . '";
		' . $this -> id . '.table_id = "' . CUtil ::JSEscape($this -> tableId) . '";
		' . $this -> id . '.presetsDeleted = [' . $this -> arOptFlt["presetsDeletedJS"] . '];';

        if ($filteredTab != null || $openedTabUri != false) {
            $tabToInit = ($openedTabUri ? $openedTabUri : $filteredTab);

            echo '
		' . $this -> id . '.InitFilteredTab("' . CUtil ::JSEscape($tabToInit) . '");';
        }

        if ($openedTabSes != null || $openedTabUri != false)
            echo '
		var openedFTab = ' . $this -> id . '.InitOpenedTab("' . CUtil ::JSEscape($openedTabUri) . '", "' . CUtil ::JSEscape($openedTabSes) . '");';

        echo '
		' . $this -> id . '.state.init = false;
		BX("adm-filter-tab-wrap-' . $this -> id . '").style.display = "block";';

        //making filter tabs draggable
        if ($this -> url) {
            $registerUrl = CHTTP ::urlDeleteParams($this -> url, array("adm_filter_applied", "adm_filter_preset"));

            foreach ($this -> arItems as $filter_id => $filter) {
                $arParamsAdd = array("adm_filter_applied" => $filter_id);

                if (isset($filter["PRESET_ID"]))
                    $arParamsAdd["adm_filter_preset"] = $filter["PRESET_ID"];

                $filterUrl = CHTTP ::urlAddParams($registerUrl, $arParamsAdd, array("encode", "skip_empty"));

                echo "
		BX.adminMenu.registerItem('adm-filter-tab-" . $this -> id . '-' . $filter_id . "', {URL:'" . $filterUrl . "', TITLE: true});";
            }
        }

        echo '
	});
</script>';
    }
}

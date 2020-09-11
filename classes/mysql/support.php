<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/muiv.support/classes/general/support.php");

class CTicket extends CAllTicket
{
    public static function isnull($field, $alternative)
    {
        return "ifnull(" . $field . "," . $alternative . ")";
    }

    public static function err_mess()
    {
        $module_id = "muiv.support";
        @include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/install/version.php");
        return "<br>Module: " . $module_id . " <br>Class: CTicket<br>File: " . __FILE__;
    }

    public static function GetList(&$by, &$order, $arFilter = array(), &$isFiltered, $checkRights = "Y", $getUserName = "Y", $getExtraNames = "Y", $siteID = false, $arParams = array())
    {
        $err_mess = (CTicket ::err_mess()) . "<br>Function: GetList<br>Line: ";
        global $DB, $USER, $USER_FIELD_MANAGER;

        /** @var string $d_join Dictionary join */
        $d_join = "";

        $bAdmin = 'N';
        $bSupportTeam = 'N';
        $bSupportClient = 'N';
        $bDemo = 'N';

        /** @var string $messJoin Messages join */
        $messJoin = "";

        /** @var string $searchJoin Search table join */
        $searchJoin = '';

        $need_group = false;

        $arSqlHaving = array();

        if ($checkRights == 'Y') {
            $bAdmin = (CTicket ::IsAdmin()) ? 'Y' : 'N';
            $bSupportTeam = (CTicket ::IsSupportTeam()) ? 'Y' : 'N';
            $bSupportClient = (CTicket ::IsSupportClient()) ? 'Y' : 'N';
            $bDemo = (CTicket ::IsDemo()) ? 'Y' : 'N';
            $uid = intval($USER -> GetID());
        } else {
            $bAdmin = 'Y';
            $bSupportTeam = 'Y';
            $bSupportClient = 'Y';
            $bDemo = 'Y';
            if (is_object($USER)) $uid = intval($USER -> GetID()); else $uid = -1;
        }
        if ($bAdmin != 'Y' && $bSupportTeam != 'Y' && $bSupportClient != 'Y' && $bDemo != 'Y') return false;

        if ($bSupportTeam == 'Y' || $bAdmin == 'Y' || $bDemo == 'Y') {
            $lamp = "
				if(ifnull(T.DATE_CLOSE,'x')<>'x', 'grey',
					if(ifnull(T.LAST_MESSAGE_USER_ID,0)='$uid', 'green',
						if(ifnull(T.OWNER_USER_ID,0)='$uid', 'red',
							if(T.LAST_MESSAGE_BY_SUPPORT_TEAM='Y','green_s',
								if(ifnull(T.RESPONSIBLE_USER_ID,0)='$uid', 'red',
									'yellow')))))
				";
        } else {
            $lamp = "
				if(ifnull(T.DATE_CLOSE,'x')<>'x', 'grey',
					if(T.LAST_MESSAGE_BY_SUPPORT_TEAM='Y', 'red', 'green'))
				";
        }
        $bJoinSupportTeamTbl = $bJoinClientTbl = false;

        $arSqlSearch = array();
        $strSqlSearch = "";

        if (is_array($arFilter)) {
            $filterKeys = array_keys($arFilter);
            $filterKeysCount = count($filterKeys);
            for ($i = 0; $i < $filterKeysCount; $i++) {
                $key = $filterKeys[$i];
                $val = $arFilter[$filterKeys[$i]];
                if ((is_array($val) && count($val) <= 0) || (!is_array($val) && (strlen($val) <= 0 || $val === 'NOT_REF')))
                    continue;
                $matchValueSet = (in_array($key . "_EXACT_MATCH", $filterKeys)) ? true : false;
                $key = strtoupper($key);
                switch ($key) {
                    case "ID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T.ID", $val, $match);
                        break;
                    case "HOLD_ON":
                        $arSqlSearch[] = ($val == "Y") ? "T.HOLD_ON='Y'" : "T.HOLD_ON = 'N'";
                        break;

                    case "LID":
                    case "SITE":
                    case "SITE_ID":
                        if (is_array($val)) $val = implode(" | ", $val);
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T.SITE_ID", $val, $match);
                        break;
                    case "LAMP":
                        if (is_array($val)) {
                            if (count($val) > 0) {
                                $str = "";
                                foreach ($val as $value) {
                                    $str .= ", '" . $DB -> ForSQL($value) . "'";
                                }
                                $str = TrimEx($str, ",");
                                $arSqlSearch[] = " " . $lamp . " in (" . $str . ")";
                            }
                        } elseif (strlen($val) > 0) {
                            $arSqlSearch[] = " " . $lamp . " = '" . $DB -> ForSQL($val) . "'";
                        }
                        break;
                    case "DATE_CREATE_1":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.DATE_CREATE>=" . $DB -> CharToDateFunction($val, "SHORT");
                        break;
                    case "DATE_CREATE_2":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.DATE_CREATE<" . $DB -> CharToDateFunction($val, "SHORT") . " + INTERVAL 1 DAY";
                        break;
                    case "DATE_TIMESTAMP_1":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.TIMESTAMP_X>=" . $DB -> CharToDateFunction($val, "SHORT");
                        break;
                    case "DATE_TIMESTAMP_2":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.TIMESTAMP_X<" . $DB -> CharToDateFunction($val, "SHORT") . " + INTERVAL 1 DAY";
                        break;
                    case "DATE_CLOSE_1":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.DATE_CLOSE>=" . $DB -> CharToDateFunction($val, "SHORT");
                        break;
                    case "DATE_CLOSE_2":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.DATE_CLOSE<" . $DB -> CharToDateFunction($val, "SHORT") . " + INTERVAL 1 DAY";
                        break;
                    case "CLOSE":
                        $arSqlSearch[] = ($val == "Y") ? "T.DATE_CLOSE is not null" : "T.DATE_CLOSE is null";
                        break;
                    case "AUTO_CLOSE_DAYS1":
                        $arSqlSearch[] = "T.AUTO_CLOSE_DAYS>='" . intval($val) . "'";
                        break;
                    case "AUTO_CLOSE_DAYS2":
                        $arSqlSearch[] = "T.AUTO_CLOSE_DAYS<='" . intval($val) . "'";
                        break;
                    case "TICKET_TIME_1":
                        $arSqlSearch[] = "UNIX_TIMESTAMP(T.DATE_CLOSE) - UNIX_TIMESTAMP(T.DATE_CREATE)>='" . (intval($val) * 86400) . "'";
                        break;
                    case "TICKET_TIME_2":
                        $arSqlSearch[] = "UNIX_TIMESTAMP(T.DATE_CLOSE) - UNIX_TIMESTAMP(T.DATE_CREATE)<='" . (intval($val) * 86400) . "'";
                        break;
                    case "TITLE":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("T.TITLE", $val, $match);
                        break;
                    case "MESSAGES1":
                        $arSqlSearch[] = "T.MESSAGES>='" . intval($val) . "'";
                        break;
                    case "MESSAGES2":
                        $arSqlSearch[] = "T.MESSAGES<='" . intval($val) . "'";
                        break;

                    case "PROBLEM_TIME1":
                        $arSqlSearch[] = "T.PROBLEM_TIME>='" . intval($val) . "'";
                        break;
                    case "PROBLEM_TIME2":
                        $arSqlSearch[] = "T.PROBLEM_TIME<='" . intval($val) . "'";
                        break;

                    case "OVERDUE_MESSAGES1":
                        $arSqlSearch[] = "T.OVERDUE_MESSAGES>='" . intval($val) . "'";
                        break;
                    case "OVERDUE_MESSAGES2":
                        $arSqlSearch[] = "T.OVERDUE_MESSAGES<='" . intval($val) . "'";
                        break;
                    case "AUTO_CLOSE_DAYS_LEFT1":
                        $arSqlSearch[] = "CASE WHEN (UNIX_TIMESTAMP(T.DATE_CLOSE) IS NULL OR UNIX_TIMESTAMP(T.DATE_CLOSE) = 0) AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN
							TO_DAYS(ADDDATE(T.LAST_MESSAGE_DATE, INTERVAL T.AUTO_CLOSE_DAYS DAY)) - TO_DAYS(now()) ELSE -1 END >='" . intval($val) . "'";
                        break;
                    case "AUTO_CLOSE_DAYS_LEFT2":
                        $arSqlSearch[] = "CASE WHEN (UNIX_TIMESTAMP(T.DATE_CLOSE) IS NULL OR UNIX_TIMESTAMP(T.DATE_CLOSE) = 0) AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN
							TO_DAYS(ADDDATE(T.LAST_MESSAGE_DATE, INTERVAL T.AUTO_CLOSE_DAYS DAY))-TO_DAYS(now()) ELSE 999 END <='" . intval($val) . "'";
                        break;
                    case "OWNER":
                        $getUserName = "Y";
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("UO.ID, UO.LOGIN, UO.LAST_NAME, UO.NAME", $val, $match, array("@", ".")); //T.OWNER_USER_ID,
                        break;
                    case "OWNER_USER_ID":
                    case "OWNER_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T." . $key, $val, $match);
                        break;
                    case "SLA_ID":
                    case "SLA":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
                        break;
                    case "CREATED_BY":
                        $getUserName = "Y";
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("T.CREATED_USER_ID, UC.LOGIN, UC.LAST_NAME, UC.NAME, T.CREATED_MODULE_NAME", $val, $match);
                        break;
                    case "RESPONSIBLE":
                        $getUserName = "Y";
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("T.RESPONSIBLE_USER_ID, UR.LOGIN, UR.LAST_NAME, UR.NAME", $val, $match);
                        break;
                    case "RESPONSIBLE_ID":
                        if (intval($val) > 0) $arSqlSearch[] = "T.RESPONSIBLE_USER_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.RESPONSIBLE_USER_ID is null or T.RESPONSIBLE_USER_ID=0)";
                        break;
                    case "CATEGORY_ID":
                    case "CATEGORY":
                        if (intval($val) > 0) $arSqlSearch[] = "T.CATEGORY_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
                        break;
                    case "CATEGORY_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("DC.SID", $val, $match);
                        $d_join = "
			LEFT JOIN b_ticket_dictionary DC ON (DC.ID = T.CATEGORY_ID and DC.C_TYPE = 'C')";
                        break;
                    case "CRITICALITY_ID":
                    case "CRITICALITY":
                        if (intval($val) > 0) $arSqlSearch[] = "T.CRITICALITY_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
                        break;
                    case "CRITICALITY_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("DK.SID", $val, $match);
                        break;
                    case "STATUS_ID":
                    case "STATUS":
                        if (intval($val) > 0) $arSqlSearch[] = "T.STATUS_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
                        break;
                    case "STATUS_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("DS.SID", $val, $match);
                        break;
                    case "MARK_ID":
                    case "MARK":
                        if (intval($val) > 0) $arSqlSearch[] = "T.MARK_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
                        break;
                    case "MARK_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("DM.SID", $val, $match);
                        break;
                    case "SOURCE_ID":
                    case "SOURCE":
                        if (intval($val) > 0) $arSqlSearch[] = "T.SOURCE_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
                        break;
                    case "SOURCE_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("DSR.SID", $val, $match);
                        break;

                    case "DIFFICULTY_ID":
                    case "DIFFICULTY":
                        if (intval($val) > 0) $arSqlSearch[] = "T.DIFFICULTY_ID = '" . intval($val) . "'";
                        elseif ($val == 0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
                        break;
                    case "DIFFICULTY_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("DD.SID", $val, $match);
                        break;


                    case "MODIFIED_BY":
                        $getUserName = "Y";
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("T.MODIFIED_USER_ID, T.MODIFIED_MODULE_NAME, UM.LOGIN, UM.LAST_NAME, UM.NAME", $val, $match);
                        break;
                    case "MESSAGE":
                        global $strError;
                        if (strlen($val) <= 0) break;

                        if (CSupportSearch ::CheckModule() && CSupportSearch ::isIndexExists()) {
                            // new indexed search
                            $searchSqlParams = CSupportSearch ::getSql($val);
                            $searchOn = $searchSqlParams['WHERE'];
                            $searchHaving = $searchSqlParams['HAVING'];

                            if ($searchOn) {
                                $searchJoin = 'INNER JOIN b_ticket_search TS ON TS.TICKET_ID = T.ID AND ' . $searchOn;

                                if (!empty($searchHaving)) {
                                    // 2 or more search words
                                    $arSqlHaving[] = $searchHaving;
                                    $need_group = true;
                                }
                            }

                        } else {
                            if ($bSupportTeam == "Y" || $bAdmin == "Y" || $bDemo == "Y") {
                                $messJoin = "INNER JOIN b_ticket_message M ON (M.TICKET_ID=T.ID)";
                            } else {
                                $messJoin = "INNER JOIN b_ticket_message M ON (M.TICKET_ID=T.ID and M.IS_HIDDEN='N' and M.IS_LOG='N')";
                            }

                            $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                            $f = new CFilterQuery("OR", "yes", $match, array(), "N", "Y", "N");
                            $query = $f -> GetQueryString("T.TITLE,M.MESSAGE_SEARCH", $val);
                            $error = $f -> error;
                            if (strlen(trim($error)) > 0) {
                                $strError .= $error . "<br>";
                                $query = "0";
                            } else $arSqlSearch[] = $query;
                        }
                        break;
                    case "LAST_MESSAGE_USER_ID":
                    case "LAST_MESSAGE_SID":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T." . $key, $val, $match);
                        break;
                    case "LAST_MESSAGE_BY_SUPPORT_TEAM":
                        $arSqlSearch[] = "T.LAST_MESSAGE_BY_SUPPORT_TEAM= '" . ($val == 'Y' ? 'Y' : 'N') . "'";
                        break;
                    case "SUPPORT_COMMENTS":
                        $match = ($arFilter[$key . "_EXACT_MATCH"] == "Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("T.SUPPORT_COMMENTS", $val, $match);
                        break;
                    case "IS_SPAM":
                        $arSqlSearch[] = ($val == "Y") ? "T.IS_SPAM ='Y'" : "(T.IS_SPAM = 'N' or T.IS_SPAM is null)";
                        break;
                    case "IS_OVERDUE":
                        $arSqlSearch[] = ($val == "Y") ? "T.IS_OVERDUE ='Y'" : "(T.IS_OVERDUE = 'N' or T.IS_OVERDUE is null)";
                        break;
                    case "IS_SPAM_MAYBE":
                        $arSqlSearch[] = ($val == "Y") ? "T.IS_SPAM='N'" : "(T.IS_SPAM='Y' or T.IS_SPAM is null)";
                        break;

                    case 'SUPPORTTEAM_GROUP_ID':
                    case 'CLIENT_GROUP_ID':
                        if ($key == 'SUPPORTTEAM_GROUP_ID') {
                            $table = 'UGS';
                            $bJoinSupportTeamTbl = true;
                        } else {
                            $table = 'UGC';
                            $bJoinClientTbl = true;
                        }
                        if (is_array($val)) {
                            $val = array_map('intval', $val);
                            $val = array_unique($val);
                            $val = array_filter($val);
                            if (count($val) > 0) {
                                $arSqlSearch[] = '(' . $table . '.GROUP_ID IS NOT NULL AND ' . $table . '.GROUP_ID IN (' . implode(',', $val) . '))';
                            }
                        } else {
                            $val = intval($val);
                            if ($val > 0) {
                                $arSqlSearch[] = '(' . $table . '.GROUP_ID IS NOT NULL AND ' . $table . '.GROUP_ID=\'' . $val . '\')';
                            }
                        }
                        break;
                    case 'COUPON':
                        $match = ($matchValueSet && $arFilter[$key . "_EXACT_MATCH"] != "Y") ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T." . $key, $val, $match);
                        break;
                }
            }
        }

        $obUserFieldsSql = new CUserTypeSQL;
        $obUserFieldsSql -> SetEntity("SUPPORT", "T.ID");
        $obUserFieldsSql -> SetSelect($arParams["SELECT"]);
        $obUserFieldsSql -> SetFilter($arFilter);
        $obUserFieldsSql -> SetOrder(array($by => $order));

        if ($by == "s_id") {
            $strSqlOrder = "ORDER BY T.ID";
        } elseif ($by == "s_last_message_date") {
            $strSqlOrder = "ORDER BY T.LAST_MESSAGE_DATE";
        } elseif ($by == "s_site_id" || $by == "s_lid") {
            $strSqlOrder = "ORDER BY T.SITE_ID";
        } elseif ($by == "s_lamp") {
            $strSqlOrder = "ORDER BY LAMP";
        } elseif ($by == "s_is_overdue") {
            $strSqlOrder = "ORDER BY T.IS_OVERDUE";
        } elseif ($by == "s_is_notified") {
            $strSqlOrder = "ORDER BY T.IS_NOTIFIED";
        } elseif ($by == "s_date_create") {
            $strSqlOrder = "ORDER BY T.DATE_CREATE";
        } elseif ($by == "s_timestamp" || $by == "s_timestamp_x") {
            $strSqlOrder = "ORDER BY T.TIMESTAMP_X";
        } elseif ($by == "s_date_close") {
            $strSqlOrder = "ORDER BY T.DATE_CLOSE";
        } elseif ($by == "s_owner") {
            $strSqlOrder = "ORDER BY T.OWNER_USER_ID";
        } elseif ($by == "s_modified_by") {
            $strSqlOrder = "ORDER BY T.MODIFIED_USER_ID";
        } elseif ($by == "s_title") {
            $strSqlOrder = "ORDER BY T.TITLE ";
        } elseif ($by == "s_responsible") {
            $strSqlOrder = "ORDER BY T.RESPONSIBLE_USER_ID";
        } elseif ($by == "s_messages") {
            $strSqlOrder = "ORDER BY T.MESSAGES";
        } elseif ($by == "s_category") {
            $strSqlOrder = "ORDER BY T.CATEGORY_ID";
        } elseif ($by == "s_criticality") {
            $strSqlOrder = "ORDER BY T.CRITICALITY_ID";
        } elseif ($by == "s_sla") {
            $strSqlOrder = "ORDER BY T.SLA_ID";
        } elseif ($by == "s_status") {
            $strSqlOrder = "ORDER BY T.STATUS_ID";
        } elseif ($by == "s_difficulty") {
            $strSqlOrder = "ORDER BY T.DIFFICULTY_ID";
        } elseif ($by == "s_problem_time") {
            $strSqlOrder = "ORDER BY T.PROBLEM_TIME";
        } elseif ($by == "s_mark") {
            $strSqlOrder = "ORDER BY T.MARK_ID";
        } elseif ($by == "s_online") {
            $strSqlOrder = "ORDER BY USERS_ONLINE";
        } elseif ($by == "s_support_comments") {
            $strSqlOrder = "ORDER BY T.SUPPORT_COMMENTS";
        } elseif ($by == "s_auto_close_days_left") {
            $strSqlOrder = "ORDER BY AUTO_CLOSE_DAYS_LEFT";
        } elseif ($by == 's_coupon') {
            $strSqlOrder = 'ORDER BY T.COUPON';
        } elseif ($by == 's_deadline') {
            $strSqlOrder = 'ORDER BY T.SUPPORT_DEADLINE';
        } elseif ($s = $obUserFieldsSql -> GetOrder($by)) {
            $strSqlOrder = "ORDER BY " . strtoupper($s);
        } else {
            $by = "s_default";
            $strSqlOrder = "ORDER BY IS_SUPER_TICKET DESC, T.IS_OVERDUE DESC, T.IS_NOTIFIED DESC, T.LAST_MESSAGE_DATE";
        }
        if ($order != "asc") {
            $strSqlOrder .= " desc ";
            $order = "desc";
        }

        $arSqlSearch[] = $obUserFieldsSql -> GetFilter();

        if ($getUserName == "Y") {
            $u_select = "
				,
				UO.LOGIN													OWNER_LOGIN,
				UO.EMAIL													OWNER_EMAIL,
				concat(ifnull(UO.NAME,''),' ',ifnull(UO.LAST_NAME,''))		OWNER_NAME,
				UR.LOGIN													RESPONSIBLE_LOGIN,
				UR.EMAIL													RESPONSIBLE_EMAIL,
				concat(ifnull(UR.NAME,''),' ',ifnull(UR.LAST_NAME,''))		RESPONSIBLE_NAME,
				UM.LOGIN													MODIFIED_BY_LOGIN,
				UM.EMAIL													MODIFIED_BY_EMAIL,
				concat(ifnull(UM.NAME,''),' ',ifnull(UM.LAST_NAME,''))		MODIFIED_BY_NAME,
				UM.LOGIN													MODIFIED_LOGIN,
				UM.EMAIL													MODIFIED_EMAIL,
				concat(ifnull(UM.NAME,''),' ',ifnull(UM.LAST_NAME,''))		MODIFIED_NAME,
				UL.LOGIN													LAST_MESSAGE_LOGIN,
				UL.EMAIL													LAST_MESSAGE_EMAIL,
				concat(ifnull(UL.NAME,''),' ',ifnull(UL.LAST_NAME,''))		LAST_MESSAGE_NAME,
				UC.LOGIN													CREATED_LOGIN,
				UC.EMAIL													CREATED_EMAIL,
				concat(ifnull(UC.NAME,''),' ',ifnull(UC.LAST_NAME,''))		CREATED_NAME
			";
            $u_join = "
			LEFT JOIN b_user UO ON (UO.ID = T.OWNER_USER_ID)
			LEFT JOIN b_user UR ON (UR.ID = T.RESPONSIBLE_USER_ID)
			LEFT JOIN b_user UM ON (UM.ID = T.MODIFIED_USER_ID)
			LEFT JOIN b_user UL ON (UL.ID = T.LAST_MESSAGE_USER_ID)
			LEFT JOIN b_user UC ON (UC.ID = T.CREATED_USER_ID)
			";
        }
        if ($getExtraNames == "Y") {
            $d_select = "
				,
				DC.NAME														CATEGORY_NAME,
				DC.DESCR													CATEGORY_DESC,
				DC.SID														CATEGORY_SID,
				DK.NAME														CRITICALITY_NAME,
				DK.DESCR													CRITICALITY_DESC,
				DK.SID														CRITICALITY_SID,
				DS.NAME														STATUS_NAME,
				DS.DESCR													STATUS_DESC,
				DS.SID														STATUS_SID,
				DM.NAME													MARK_NAME,
				DM.DESCR													MARK_DESC,
				DM.SID														MARK_SID,
				DSR.NAME													SOURCE_NAME,
				DSR.DESCR													SOURCE_DESC,
				DSR.SID														SOURCE_SID,
				DD.NAME													DIFFICULTY_NAME,
				DD.DESCR													DIFFICULTY_DESC,
				DD.SID														DIFFICULTY_SID,
				SLA.NAME													SLA_NAME
			";
            $d_join = "
			LEFT JOIN b_ticket_dictionary DC ON (DC.ID = T.CATEGORY_ID and DC.C_TYPE = 'C')
			LEFT JOIN b_ticket_dictionary DK ON (DK.ID = T.CRITICALITY_ID and DK.C_TYPE = 'K')
			LEFT JOIN b_ticket_dictionary DS ON (DS.ID = T.STATUS_ID and DS.C_TYPE = 'S')
			LEFT JOIN b_ticket_dictionary DM ON (DM.ID = T.MARK_ID and DM.C_TYPE = 'M')
			LEFT JOIN b_ticket_dictionary DSR ON (DSR.ID = T.SOURCE_ID and DSR.C_TYPE = 'SR')
			LEFT JOIN b_ticket_dictionary DD ON (DD.ID = T.DIFFICULTY_ID and DD.C_TYPE = 'D')
			LEFT JOIN b_ticket_sla SLA ON (SLA.ID = T.SLA_ID)
			";
        }
        if (strlen($siteID) > 0) {
            $dates_select = "
				" . $DB -> DateToCharFunction("T.DATE_CREATE", "FULL", $siteID, true) . "	DATE_CREATE,
				" . $DB -> DateToCharFunction("T.TIMESTAMP_X", "FULL", $siteID, true) . "	TIMESTAMP_X,
				" . $DB -> DateToCharFunction("T.LAST_MESSAGE_DATE", "FULL", $siteID, true) . "	LAST_MESSAGE_DATE,
				" . $DB -> DateToCharFunction("T.DATE_CLOSE", "FULL", $siteID, true) . "	DATE_CLOSE,
				" . $DB -> DateToCharFunction("T.DATE_CREATE", "SHORT", $siteID, true) . "	DATE_CREATE_SHORT,
				" . $DB -> DateToCharFunction("T.TIMESTAMP_X", "SHORT", $siteID, true) . "	TIMESTAMP_X_SHORT,
				" . $DB -> DateToCharFunction("T.DATE_CLOSE", "SHORT", $siteID, true) . "	DATE_CLOSE_SHORT,
				" . $DB -> DateToCharFunction("T.SUPPORT_DEADLINE", "FULL", $siteID, true) . "	SUPPORT_DEADLINE,
				CASE WHEN (UNIX_TIMESTAMP(T.DATE_CLOSE) IS NULL OR UNIX_TIMESTAMP(T.DATE_CLOSE) = 0) AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN "
                . $DB -> DateToCharFunction("ADDDATE(T.LAST_MESSAGE_DATE, INTERVAL T.AUTO_CLOSE_DAYS DAY)", "FULL", $siteID, true)
                . " ELSE NULL END AUTO_CLOSE_DATE
			";
        } else {
            $dates_select = "
				" . $DB -> DateToCharFunction("T.DATE_CREATE", "FULL") . "		DATE_CREATE,
				" . $DB -> DateToCharFunction("T.TIMESTAMP_X", "FULL") . "		TIMESTAMP_X,
				" . $DB -> DateToCharFunction("T.LAST_MESSAGE_DATE", "FULL") . "	LAST_MESSAGE_DATE,
				" . $DB -> DateToCharFunction("T.DATE_CLOSE", "FULL") . "		DATE_CLOSE,
				" . $DB -> DateToCharFunction("T.DATE_CREATE", "SHORT") . "	DATE_CREATE_SHORT,
				" . $DB -> DateToCharFunction("T.TIMESTAMP_X", "SHORT") . "	TIMESTAMP_X_SHORT,
				" . $DB -> DateToCharFunction("T.DATE_CLOSE", "SHORT") . "		DATE_CLOSE_SHORT,
				" . $DB -> DateToCharFunction("T.SUPPORT_DEADLINE", "FULL") . "	SUPPORT_DEADLINE,
				CASE WHEN (UNIX_TIMESTAMP(T.DATE_CLOSE) IS NULL OR UNIX_TIMESTAMP(T.DATE_CLOSE) = 0) AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN "
                . $DB -> DateToCharFunction("ADDDATE(T.LAST_MESSAGE_DATE, INTERVAL T.AUTO_CLOSE_DAYS DAY)", "FULL")
                . " ELSE NULL END AUTO_CLOSE_DATE
			";
        }

        $ugroupJoin = '';

        if ($bJoinSupportTeamTbl) {
            $ugroupJoin .= "
			LEFT JOIN b_ticket_user_ugroup UGS ON (UGS.USER_ID = T.RESPONSIBLE_USER_ID) ";
            $need_group = true;
        }

        if ($bJoinClientTbl) {
            $ugroupJoin .= "
			LEFT JOIN b_ticket_user_ugroup UGC ON (UGC.USER_ID = T.OWNER_USER_ID) ";
            $need_group = true;
        }

        // add permissions check
        if (!($bAdmin == 'Y' || $bDemo == 'Y')) {
            // a list of users who own or are responsible for tickets, which we can show to our current user
            $ticketUsers = array($uid);

            // check if user has groups
            $result = $DB -> Query('SELECT GROUP_ID FROM b_ticket_user_ugroup WHERE USER_ID = ' . $uid . ' AND CAN_VIEW_GROUP_MESSAGES = \'Y\'');
            if ($result) {
                // collect members of these groups
                $uGroups = array();

                while ($row = $result -> Fetch()) {
                    $uGroups[] = $row['GROUP_ID'];
                }

                if (!empty($uGroups)) {
                    $result = $DB -> Query('SELECT USER_ID FROM b_ticket_user_ugroup WHERE GROUP_ID IN (' . join(',', $uGroups) . ')');
                    if ($result) {
                        while ($row = $result -> Fetch()) {
                            $ticketUsers[] = $row['USER_ID'];
                        }
                    }
                }
            }

            // build sql
            $strSqlSearchUser = "";

            if ($bSupportTeam == 'Y') {
                $strSqlSearchUser = 'T.RESPONSIBLE_USER_ID IN (' . join(',', $ticketUsers) . ')';
            } elseif ($bSupportClient == 'Y') {
                $strSqlSearchUser = 'T.OWNER_USER_ID IN (' . join(',', $ticketUsers) . ')';
            }

            $arSqlSearch[] = $strSqlSearchUser;
        }

        $strSqlSearch = GetFilterSqlSearch($arSqlSearch);
        $onlineInterval = intval(COption ::GetOptionString("support", "ONLINE_INTERVAL"));

        $strSqlSelect = "
			SELECT
				T.*,
				T.SITE_ID,
				T.SITE_ID																			LID,
				$dates_select,
				UNIX_TIMESTAMP(T.DATE_CLOSE)-UNIX_TIMESTAMP(T.DATE_CREATE)							TICKET_TIME,
				CASE WHEN (UNIX_TIMESTAMP(T.DATE_CLOSE) IS NULL OR UNIX_TIMESTAMP(T.DATE_CLOSE) = 0) AND T.LAST_MESSAGE_BY_SUPPORT_TEAM = 'Y' THEN
					TO_DAYS(
						ADDDATE(
							T.LAST_MESSAGE_DATE, INTERVAL T.AUTO_CLOSE_DAYS DAY
						)
					) - TO_DAYS(now())
				ELSE -1 END AUTO_CLOSE_DAYS_LEFT,
				(SELECT COUNT(DISTINCT USER_ID) FROM b_ticket_online WHERE TICKET_ID = T.ID AND TIMESTAMP_X >= DATE_ADD(now(), INTERVAL - " . $onlineInterval . " SECOND)) USERS_ONLINE,
				if(T.COUPON IS NOT NULL, 1, 0)														IS_SUPER_TICKET,
				$lamp																				LAMP
				$d_select
				$u_select
				" . $obUserFieldsSql -> GetSelect();

        $strSqlFrom = "
			FROM
				b_ticket T
			$u_join
			$d_join
			$messJoin
			$searchJoin
			$ugroupJoin
				" . $obUserFieldsSql -> GetJoin("T.ID");

        $strSqlWhere = "
			WHERE
			$strSqlSearch
		";

        $strSqlGroup = $need_group ? ' GROUP BY T.ID  ' : '';
        $strSqlHaving = $arSqlHaving ? ' HAVING ' . join(' AND ', $arSqlHaving) . ' ' : '';

        $strSql = $strSqlSelect . $strSqlFrom . $strSqlWhere . $strSqlGroup . $strSqlHaving . $strSqlOrder;

        if (is_array($arParams) && isset($arParams["NAV_PARAMS"]) && is_array($arParams["NAV_PARAMS"])) {
            $nTopCount = isset($arParams['NAV_PARAMS']['nTopCount']) ? intval($arParams['NAV_PARAMS']['nTopCount']) : 0;

            if ($nTopCount > 0) {
                $strSql = $DB -> TopSql($strSql, $nTopCount);
                $res = $DB -> Query($strSql, false, $err_mess . __LINE__);
                $res -> SetUserFields($USER_FIELD_MANAGER -> GetUserFields("SUPPORT"));
            } else {
                $cntSql = "SELECT COUNT(T.ID) as C " . $strSqlFrom . $strSqlWhere . $strSqlGroup . $strSqlHaving;

                if (!empty($strSqlGroup)) {
                    $cntSql = 'SELECT COUNT(1) AS C FROM (' . $cntSql . ') tt';
                }

                $res_cnt = $DB -> Query($cntSql);
                $res_cnt = $res_cnt -> Fetch();
                $res = new CDBResult();
                $res -> SetUserFields($USER_FIELD_MANAGER -> GetUserFields("SUPPORT"));
                $res -> NavQuery($strSql, $res_cnt["C"], $arParams["NAV_PARAMS"]);
            }
        } else {
            $res = $DB -> Query($strSql, false, $err_mess . __LINE__);
            $res -> SetUserFields($USER_FIELD_MANAGER -> GetUserFields("SUPPORT"));
        }

        $isFiltered = (IsFiltered($strSqlSearch));
        return $res;
    }

    public static function GetDynamicList(&$by, &$order, $arFilter=Array())
    {
        $err_mess = (CTicket::err_mess())."<br>Function: GetDynamicList<br>Line: ";
        global $DB;
        $arSqlSearch = Array();
        $strSqlSearch = "";
        if (is_array($arFilter))
        {
            $filterKeys = array_keys($arFilter);
            $filterKeysCount = count($filterKeys);
            for ($i=0; $i<$filterKeysCount; $i++)
            {
                $key = $filterKeys[$i];
                $val = $arFilter[$filterKeys[$i]];
                if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
                    continue;
                $matchValueSet = (in_array($key."_EXACT_MATCH", $filterKeys)) ? true : false;
                $key = strtoupper($key);
                switch($key)
                {
                    case "DATE_CREATE_1":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.DATE_CREATE>=".$DB->CharToDateFunction($val, "SHORT");
                        break;
                    case "DATE_CREATE_2":
                        if (CheckDateTime($val))
                            $arSqlSearch[] = "T.DATE_CREATE<".$DB->CharToDateFunction($val, "SHORT")." + INTERVAL 1 DAY";
                        break;
                    case "RESPONSIBLE":
                        $match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $matchValueSet) ? "N" : "Y";
                        $arSqlSearch[] = GetFilterQuery("T.RESPONSIBLE_USER_ID, UR.LOGIN, UR.LAST_NAME, UR.NAME", $val, $match);
                        break;
                    case "RESPONSIBLE_ID":
                        if (intval($val)>0) $arSqlSearch[] = "T.RESPONSIBLE_USER_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.RESPONSIBLE_USER_ID is null or T.RESPONSIBLE_USER_ID=0)";
                        break;
                    case "SLA_ID":
                    case "SLA":
                        $match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $matchValueSet) ? "Y" : "N";
                        $arSqlSearch[] = GetFilterQuery("T.SLA_ID", $val, $match);
                        break;
                    case "CATEGORY_ID":
                    case "CATEGORY":
                        if (intval($val)>0) $arSqlSearch[] = "T.CATEGORY_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.CATEGORY_ID is null or T.CATEGORY_ID=0)";
                        break;
                    case "CRITICALITY_ID":
                    case "CRITICALITY":
                        if (intval($val)>0) $arSqlSearch[] = "T.CRITICALITY_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.CRITICALITY_ID is null or T.CRITICALITY_ID=0)";
                        break;
                    case "STATUS_ID":
                    case "STATUS":
                        if (intval($val)>0) $arSqlSearch[] = "T.STATUS_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.STATUS_ID is null or T.STATUS_ID=0)";
                        break;
                    case "MARK_ID":
                    case "MARK":
                        if (intval($val)>0) $arSqlSearch[] = "T.MARK_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.MARK_ID is null or T.MARK_ID=0)";
                        break;
                    case "SOURCE_ID":
                    case "SOURCE":
                        if (intval($val)>0) $arSqlSearch[] = "T.SOURCE_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.SOURCE_ID is null or T.SOURCE_ID=0)";
                        break;
                    case "DIFFICULTY_ID":
                    case "DIFFICULTY":
                        if (intval($val)>0) $arSqlSearch[] = "T.DIFFICULTY_ID = '".intval($val)."'";
                        elseif ($val==0) $arSqlSearch[] = "(T.DIFFICULTY_ID is null or T.DIFFICULTY_ID=0)";
                        break;
                }
            }
        }
        $strSqlSearch = GetFilterSqlSearch($arSqlSearch);
        if ($by == "s_date_create") $strSqlOrder = "ORDER BY T.DATE_CREATE";
        else
        {
            $by = "s_date_create";
            $strSqlOrder = "ORDER BY T.DATE_CREATE";
        }
        if ($order!="asc")
        {
            $strSqlOrder .= " desc ";
            $order="desc";
        }
        $strSql = "
			SELECT
				count(T.ID)							ALL_TICKETS,
				sum(if(T.DATE_CLOSE is null,1,0))	OPEN_TICKETS,
				sum(if(T.DATE_CLOSE is null,0,1))	CLOSE_TICKETS,
				DAYOFMONTH(T.DAY_CREATE)			CREATE_DAY,
				MONTH(T.DAY_CREATE)					CREATE_MONTH,
				YEAR(T.DAY_CREATE)					CREATE_YEAR
			FROM
				b_ticket T
			LEFT JOIN b_user UR ON (T.RESPONSIBLE_USER_ID = UR.ID)
			WHERE
			$strSqlSearch
			and	T.DAY_CREATE is not null
			GROUP BY
				TO_DAYS(T.DAY_CREATE)
			$strSqlOrder
			";

        $res = $DB->Query($strSql, false, $err_mess.__LINE__);
        return $res;
    }
}

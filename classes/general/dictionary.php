<?
IncludeModuleLangFile(__FILE__);

class CAllTicketDictionary
{
    function err_mess()
    {
        $module_id = "muiv.support";
        @include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/install/version.php");
        return "<br>Module: " . $module_id . " <br>Class: CAllTicketDictionary<br>File: " . __FILE__;
    }

    function GetDropDown($type="C", $siteID=false, $sla_id=false)
    {
        $err_mess = (CAllTicketDictionary::err_mess())."<br>Function: GetDropDown<br>Line: ";
        global $DB;
        if ($siteID==false || $siteID=="all")
        {
            $siteID = "";
        }
        $arFilter = array("TYPE" => $type, "SITE" => $siteID);
        $v2 = $v3 = null;
        $rs = CTicketDictionary::GetList(($v1="s_dropdown"), $v2, $arFilter, $v3);

        $oldFunctionality = COption::GetOptionString( "support", "SUPPORT_OLD_FUNCTIONALITY", "Y" );
        if( intval( $sla_id ) <= 0 || $oldFunctionality != "Y" || ( $type != "C" && $type!="K" && $type!="M" ) ) return $rs;

        switch($type)
        {
            case "C": $strSql = "SELECT CATEGORY_ID as DID FROM b_ticket_sla_2_category WHERE SLA_ID=" . intval( $sla_id ); break;
            case "K": $strSql = "SELECT CRITICALITY_ID as DID FROM b_ticket_sla_2_criticality WHERE SLA_ID=" . intval( $sla_id ); break;
            case "M": $strSql = "SELECT MARK_ID as DID FROM b_ticket_sla_2_mark WHERE SLA_ID=" . intval( $sla_id ); break;
        }
        $r = $DB->Query( $strSql, false, $err_mess . __LINE__ );
        while( $a = $r->Fetch() ) $arDID[] = $a["DID"];
        $arRecords = array();
        while( $ar = $rs->Fetch() ) if( is_array( $arDID ) && ( in_array( $ar["ID"], $arDID ) || in_array( 0,$arDID ) ) ) $arRecords[] = $ar;

        $rs = new CDBResult;
        $rs->InitFromArray($arRecords);

        return $rs;
    }
}

?>

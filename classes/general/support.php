<?
IncludeModuleLangFile(__FILE__);

global $SUPPORT_CACHE_USER_ROLES;
$SUPPORT_CACHE_USER_ROLES = array();

class CAllTicket
{

    const ADD = "ADD";
    const UPDATE = "UPDATE";
    const DELETE = "DELETE";
    const IGNORE = "IGNORE";
    const REOPEN = "REOPEN";
    const NEW_SLA = "NEW_SLA";

    public static function err_mess()
    {
        $module_id = "support";
        @include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $module_id . "/install/version.php");
        return "<br>Module: " . $module_id . " <br>Class: CAllTicket<br>File: " . __FILE__;
    }

    /***************************************************************
     *
     * ������ ������� �� ������ � ������ �� ������
     *
     * �������������� �����:
     *
     * D - ������ ������
     * R - ������ ������������
     * T - ��������� ������������
     * V - ����-������
     * W - ������������� ������������
     *****************************************************************/

    public static function GetSupportTeamRoleID()
    {
        return "T";
    }

    public static function GetDemoRoleID()
    {
        return "V";
    }

    public static function GetAdminRoleID()
    {
        return "W";
    }

    public static function GetSupportClientRoleID()
    {
        return "R";
    }

    // ���������� true ���� �������� ������������ ����� �������� ���� �� ������
    public static function HaveRole($role, $userID = false)
    {
        global $DB, $USER, $APPLICATION, $SUPPORT_CACHE_USER_ROLES;
        if (!is_object($USER)) $USER = new CUser;

        if ($userID === false && is_object($USER))
            $uid = $USER -> GetID();
        else
            $uid = $userID;

        $arRoles = array();
        if (array_key_exists($uid, $SUPPORT_CACHE_USER_ROLES) && is_array($SUPPORT_CACHE_USER_ROLES[$uid])) {
            $arRoles = $SUPPORT_CACHE_USER_ROLES[$uid];
        } else {
            $arrGroups = array();
            if ($userID === false && is_object($USER))
                $arrGroups = $USER -> GetUserGroupArray();
            else
                $arrGroups = CUser ::GetUserGroup($userID);

            sort($arrGroups);
            $arRoles = $APPLICATION -> GetUserRoles("support", $arrGroups);
            $SUPPORT_CACHE_USER_ROLES[$uid] = $arRoles;
        }

        if (in_array($role, $arRoles))
            return true;

        return false;

    }

    // true - ���� ������������ ����� ���� "������������� ������������"
    // false - � ��������� ������
    public static function IsAdmin($userID = false)
    {
        global $USER;

        if ($userID === false && is_object($USER)) {
            if ($USER -> IsAdmin()) return true;
        }
        return CTicket ::HaveRole(CTicket ::GetAdminRoleID(), $userID);
    }

    // true - ���� ������������ ����� ���� "����-������"
    // false - � ��������� ������
    public static function IsDemo($userID = false)
    {
        return CTicket ::HaveRole(CTicket ::GetDemoRoleID(), $userID);
    }

    // true - ���� ������������ ����� ���� "��������� ������������"
    // false - � ��������� ������
    public static function IsSupportTeam($userID = false)
    {
        return CTicket ::HaveRole(CTicket ::GetSupportTeamRoleID(), $userID);
    }

    public static function IsSupportClient($userID=false)
    {
        return CTicket::HaveRole(CTicket::GetSupportClientRoleID(), $userID);
    }


}

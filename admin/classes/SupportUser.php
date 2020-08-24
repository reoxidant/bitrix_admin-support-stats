<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CUser;

class SupportUser
{
    private $arrSupportUser;
    /**
     * @var array
     */
    private $arUsersID;
    /**
     * @var string
     */
    private $strUsers;

    public function addSupportUsers(){
        $titleId = "ID";
        $order = "asc";
        $rs = CUser :: GetList($titleId, $order, array("ID" => $this->strUsers), array("FIELDS" => array("NAME", "LAST_NAME", "LOGIN", "ID")));
        while ($ar = $rs -> Fetch()) {
            $this->arrSupportUser[$ar["ID"]] = $ar;
        }
    }

    public function setSupportsUsersID($arUsersID){
        $this->arUsersID = array_unique($arUsersID);
        $this->strUsers = implode("|", $arUsersID);
    }

    /**
     * @param mixed $arrSupportUser
     */
    public function setArrSupportUser($arrSupportUser): void
    {
        $this -> arrSupportUser = $arrSupportUser;
    }

    /**
     * @return mixed
     */
    public function getArrSupportUser()
    {
        return $this -> arrSupportUser;
    }
}

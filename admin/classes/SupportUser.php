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
        $rs = CUser ::GetList($titleId, $order, array("ID" => $this->strUsers), array("FIELDS" => array("NAME", "LAST_NAME", "LOGIN", "ID")));
        while ($ar = $rs -> Fetch()) {
            $this->arrSupportUser[$ar["ID"]] = $ar;
        }
    }

    public function setSupportsUsersID($arTicketUsersID){
        $this->arUsersID = array_unique($arTicketUsersID);
        $this->strUsers = implode("|", $arTicketUsersID);
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

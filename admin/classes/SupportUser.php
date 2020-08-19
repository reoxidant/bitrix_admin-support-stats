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
    public $arrSupportUser;

    private $arUsersID;

    private $strUsers;

    public function __construct($arTicketUsersID){
        $this->arUsersID = array_unique($arTicketUsersID);
        $this->strUsers = implode("|", $arTicketUsersID);
    }

    public function addSupportUsers(){
        $titleId = "ID";
        $order = "asc";
        $rs = CUser ::GetList($titleId, $order, array("ID" => $this->strUsers), array("FIELDS" => array("NAME", "LAST_NAME", "LOGIN", "ID")));
        while ($ar = $rs -> Fetch()) {
            $this->arrSupportUser[$ar["ID"]] = $ar;
        }
    }
}

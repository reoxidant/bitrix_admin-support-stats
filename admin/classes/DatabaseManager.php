<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CTicket;

class DatabaseManager
{
    public function getListTicket($by, $order, $arFilter, $is_filtered, $checkRights, $getUserName, $getExtraName)
    {
        return CTicket ::GetList($by, $order, $arFilter, $is_filtered, $checkRights, $getUserName, $getExtraName);
    }
}
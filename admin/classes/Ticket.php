<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CTicket;

/**
 * Class Ticket
 * @package admin\classes
 */
class Ticket implements PropertyContainerInterface
{
    /**
     * @var array
     */
    private $propertyContainer = [];

    /**
     * @var
     */
    private $openTickets;
    /**
     * @var
     */
    private $closeTickets;

    /**
     * @var array
     */
    public $arTicketUsersID;

    /**
     * Ticket constructor.
     */
    public function __construct()
    {
        $this -> openTickets = 0;
        $this -> closeTickets = 0;
        $this -> arTicketUsersID = [];
    }

    /**
     * @param $name
     * @param $filterProperty
     */
    public function addListTicketsDB($name, $filterProperty)
    {
        $this -> propertyContainer[$name] = CTicket ::GetList($by, $order, $filterProperty, $is_filtered, "Y", "N", "N");
    }

    /**
     * @param $name
     * @param $defaultKeysOfArray
     * @param null $defaultValueOfArray
     */
    function addDefaultPropertyByKeys($name, $defaultKeysOfArray, $defaultValueOfArray = null)
    {
        $arr = [];
        foreach ($defaultKeysOfArray as $val) {
            $arr += [$val => $defaultValueOfArray];
        }
        $this -> propertyContainer[$name] = $arr ?? null;
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

    /**
     *
     */
    public function getAllProperties()
    {
        return $this -> propertyContainer ?? null;
    }

    /**
     * @param $propName
     * @param $PREV_CREATE
     */
    public function addAdditionalDataInto($propName, $PREV_CREATE)
    {
        while ($arTicket = $this -> getProperty($propName) -> Fetch()) {
            if ($arTicket["DATE_CREATE_SHORT"] != $PREV_CREATE && strlen($PREV_CREATE) > 0) {
                $this -> addProperty('show_graph', "Y");
            }

            if (strlen($arTicket["DATE_CLOSE"]) <= 0) {
                $this -> openTickets++;
            } else {
                $this -> closeTickets++;
                $day_sec = 86400;
                $TT = $arTicket["TICKET_TIME"];

                $arrTime = $this -> getProperty('arrTime');
                $arrMess = $this -> getProperty('arrMess');

                if ($TT < $day_sec) $arrTime["1"] += 1;
                if ($TT > $day_sec && $TT <= 2 * $day_sec) $arrTime["1_2"] += 1;
                if ($TT > 2 * $day_sec && $TT <= 3 * $day_sec) $arrTime["2_3"] += 1;
                if ($TT > 3 * $day_sec && $TT <= 4 * $day_sec) $arrTime["3_4"] += 1;
                if ($TT > 4 * $day_sec && $TT <= 5 * $day_sec) $arrTime["4_5"] += 1;
                if ($TT > 5 * $day_sec && $TT <= 6 * $day_sec) $arrTime["5_6"] += 1;
                if ($TT > 6 * $day_sec && $TT <= 7 * $day_sec) $arrTime["6_7"] += 1;
                if ($TT > 7 * $day_sec) $arrTime["7"] += 1;

                $MC = $arTicket["MESSAGES"];

                if ($MC <= 2) $arrMess["2_m"] += 1;
                elseif ($MC >= 10) $arrMess["10_m"] += 1;
                else $arrMess[$MC . "_m"] += 1;
            }

            $PREV_CREATE = $arTicket["DATE_CREATE_SHORT"];

            if (intval($arTicket["RESPONSIBLE_USER_ID"]) > 0) {
                $this -> arTicketUsersID = intval($arTicket["RESPONSIBLE_USER_ID"]);
            }
        }
    }
}
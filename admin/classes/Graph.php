<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CAdminMessage;
use Protobuf\Exception;

require_once('PropertyContainerInterface.php');

/**
 * Class Graph
 * @package admin\classes
 */
class Graph implements PropertyContainerInterface
{
    /**
     * @var array
     */
    private $propertyContainer = [];

    /**
     * @param $name
     * @param $value
     * @param bool $returnValue
     * @return null
     */
    public function addProperty($name, $value, $returnValue = false)
    {
        $this -> propertyContainer[$name] = $value;

        if ($returnValue) {
            return $value;
        }

        return null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getProperty($name)
    {
        return $this -> propertyContainer[$name] ?? null;
    }

    public function getFilterParams($arrVars="filter_", $bDoHtmlEncode=true, $button = array("filter" => "Y", "set_filter" => "Y"))
    {
        $res="";

        if(is_array($arrVars) && count($arrVars)>0)
        {
            foreach($arrVars as $var_name => $value)
            {
                if(is_array($value))
                {
                    if(count($value)>0)
                    {
                        reset($value);
                        foreach($value as $v)
                            $res .= "&".urlencode($var_name)."[]=".urlencode($v);
                    }
                }
                else if(strlen($value)>0 && $value!="NOT_REF")
                {
                    $res .= "&".urlencode($var_name)."=".urlencode($value);
                }
            }
        }else{
            throw new Exception('Error: The first argument function must be type of Array');
        }

        if(is_array($button))
        {
            reset($button); // php bug
            while(list($key, $value) = each($button))
                $res .= "&".$key."=".urlencode($value);
        }
        else
            $res .= $button;

        return ($bDoHtmlEncode) ? htmlspecialcharsbx($res) : $res;
    }

    /**
     * @param $show_graph
     * @param $arFilterFields
     * @param $imageArFilter
     * @param $arrColor
     * @param string $width
     * @param string $height
     * @throws Exception
     */
    public function createImageGraph($show_graph, $arFilterFields, $imageArFilter, $arrColor, $width = "576", $height = "400")
    {
        list(
            'find_work_in' => $find_work_in,
            'find_close_ticket' => $find_close_ticket,
            'find_wait_answer_dit' => $find_wait_answer_dit,
            'find_wait_answer_user' => $find_wait_answer_user,
        ) = ($imageArFilter['data']) ? $imageArFilter['data'] : $imageArFilter['emergency'];

        if (!function_exists("ImageCreate")) : CAdminMessage :: ShowMessage(GetMessage("SUP_GD_NOT_INSTALLED"));
        elseif
        ($show_graph == "Y") :?>
            <div class="graph">
                <table border="0" cellspacing="0" cellpadding="0" class="graph">
                    <tr>
                        <td>
                            <table border="0" cellspacing="1" cellpadding="0">
                                <tr>
                                    <td>
                                        <table cellpadding="1" cellspacing="0" border="0">
                                            <tr>
                                                <td valign="center" nowrap>
                                                    <img
                                                            src="/bitrix/admin/graph_stats.php?<?= ($imageArFilter['data']) ? $this->getFilterParams($imageArFilter['data']) : GetFilterParams($arFilterFields) ?>&width=<?= $width ?>&height=<?= $height ?>&lang=<? echo LANG ?>"
                                                            width="<?= $width ?>"
                                                            height="<?= $height ?>"
                                                            alt="graph-image"
                                                    >
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table cellpadding="3" cellspacing="1" border="0" class="legend">
                                <? if ($find_work_in == "Y"): ?>
                                    <tr>
                                        <td valign="center">
                                            <img
                                                    src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["OPEN_TICKET"] ?>"
                                                    width="45"
                                                    height="2"
                                                    alt="find-work-in"
                                            >
                                        </td>
                                        <td nowrap><?= GetMessage("SUP_WORK_IN") ?></td>
                                    </tr>
                                <? endif; ?>
                                <? if ($find_close_ticket == "Y"): ?>
                                    <tr>
                                        <td valign="center">
                                            <img
                                                    src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["ALL_TICKET"] ?>"
                                                    width="45"
                                                    height="2"
                                                    alt="find-close-ticket"
                                            >
                                        </td>
                                        <td nowrap><?= GetMessage("SUP_CLOSE_TICKET") ?></td>
                                    </tr>
                                <? endif; ?>
                                <? if ($find_wait_answer_dit == "Y"): ?>
                                    <tr>
                                        <td valign="center">
                                            <img
                                                    src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["MESSAGES"] ?>"
                                                    width="45"
                                                    height="2"
                                                    alt="find-wait-answer-dit "
                                            >
                                        </td>
                                        <td nowrap><?= GetMessage("SUP_WAIT_ANSWER_DIT") ?></td>
                                    </tr>
                                <? endif; ?>
                                <? if ($find_wait_answer_user == "Y"): ?>
                                    <tr>
                                        <td valign="center">
                                            <img
                                                    src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["OVERDUE_MESSAGES"] ?>"
                                                    width="45"
                                                    height="2"
                                                    alt="find-wait-answer-user"
                                            >
                                        </td>
                                        <td nowrap><?= GetMessage("SUP_WAIT_ANSWER_USER") ?></td>
                                    </tr>
                                <? endif; ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        <?
        else:
            CAdminMessage :: ShowMessage(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_GRAPH"));
        endif;
    }

}
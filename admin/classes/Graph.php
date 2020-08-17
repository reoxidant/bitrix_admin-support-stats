<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CAdminMessage;

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
     * @return array|null
     */
    public function getAllProperties()
    {
        return $this -> propertyContainer ?? null;
    }

    /**
     * @param $show_graph
     * @param $arFilterFields
     */
    public function createImageGraph($show_graph, $arFilterFields)
    {
        if (!function_exists("ImageCreate")) :
            CAdminMessage ::ShowMessage(GetMessage("SUP_GD_NOT_INSTALLED"));
        elseif ($show_graph == "Y") :
            $width = "576";
            $height = "400";
            ?>
            <div class="graph">
                <table border="0" cellspacing="0" cellpadding="0" class="graph">
                    <tr>
                        <td>

                            <table border="0" cellspacing="1" cellpadding="0">
                                <tr>
                                    <td>
                                        <table cellpadding="1" cellspacing="0" border="0">
                                            <tr>
                                                <td valign="center" nowrap><img
                                                        src="/bitrix/admin/ticket_graph.php?<?= GetFilterParams($arFilterFields) ?>&width=<?= $width ?>&height=<?= $height ?>&lang=<? echo LANG ?>"
                                                        width="<?= $width ?>" height="<?= $height ?>"></td>
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
                                <? if ($find_open == "Y"):?>
                                    <tr>
                                        <td valign="center"><img
                                                src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["OPEN_TICKET"] ?>"
                                                width="45" height="2"></td>
                                        <td nowrap><?= GetMessage("SUP_OPEN_TICKET") ?></td>
                                    </tr>
                                <?endif; ?>
                                <? if ($find_close == "Y"):?>
                                    <tr>
                                        <td valign="center"><img
                                                src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["CLOSE_TICKET"] ?>"
                                                width="45" height="2"></td>
                                        <td nowrap><?= GetMessage("SUP_CLOSE_TICKET") ?></td>
                                    </tr>
                                <?endif; ?>
                                <? if ($find_all == "Y"):?>
                                    <tr>
                                        <td valign="center"><img
                                                src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["ALL_TICKET"] ?>"
                                                width="45" height="2"></td>
                                        <td nowrap><?= GetMessage("SUP_ALL_TICKET") ?></td>
                                    </tr>
                                <?endif; ?>
                                <? if ($find_mess == "Y"):?>
                                    <tr>
                                        <td valign="center"><img
                                                src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["MESSAGES"] ?>"
                                                width="45" height="2"></td>
                                        <td nowrap><?= GetMessage("SUP_MESSAGES") ?></td>
                                    </tr>
                                <?endif; ?>
                                <? if ($find_overdue_mess == "Y"):?>
                                    <tr>
                                        <td valign="center"><img
                                                src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["OVERDUE_MESSAGES"] ?>"
                                                width="45" height="2"></td>
                                        <td nowrap><?= GetMessage("SUP_OVERDUE_MESSAGES") ?></td>
                                    </tr>
                                <?endif; ?>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        <?
        else:
            CAdminMessage ::ShowMessage(GetMessage("SUP_NOT_ENOUGH_DATA_FOR_GRAPH"));
        endif;
    }

}
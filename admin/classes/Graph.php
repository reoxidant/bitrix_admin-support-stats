<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CAdminMessage;
use CTicketDictionary;

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

    /**
     * @param $show_graph
     * @param $arFilterFields
     * @param $arrColor
     * @param string $width
     * @param string $height
     */
    public function createImageGraph($show_graph, $arFilterFields, $status_id, $arrColor, $width = "576", $height = "400")
    {

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
                                                            src="/bitrix/admin/muiv_graph.php?<?= GetFilterParams($arFilterFields) ?>&width=<?= $width ?>&height=<?= $height ?>&lang=<? echo LANG ?>"
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
                                <?
                                $z = CTicketDictionary ::GetDropDown("S");
                                while ($zr = $z -> Fetch()) {
                                    if ($zr["REFERENCE_ID"] == $status_id):
                                        ?>
                                        <tr>
                                            <td valign="center">
                                                <img
                                                        src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["OPEN_TICKET"] ?>"
                                                        width="45"
                                                        height="2"
                                                        alt="line-graph-<?= $zr["REFERENCE_ID"] ?>"
                                                >
                                            </td>
                                            <td nowrap><?= $zr["REFERENCE"] ?></td>
                                        </tr>
                                    <? elseif ($status_id == null): ?>
                                        <tr>
                                            <td valign="center">
                                                <img
                                                        src="/bitrix/admin/ticket_graph_legend.php?color=<?= $arrColor["OPEN_TICKET"] ?>"
                                                        width="45"
                                                        height="2"
                                                        alt="line-graph-<?= $zr["REFERENCE_ID"] ?>"
                                                >
                                            </td>
                                            <td nowrap><?= $zr["REFERENCE"] ?></td>
                                        </tr>
                                    <?
                                    endif;
                                }
                                ?>
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
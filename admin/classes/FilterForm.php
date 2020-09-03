<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

use CAdminCalendar;
use CHotKeys;
use CHTTP;
use CTicketDictionary;

/**
 * Class FilterForm
 * @package admin\classes
 */
class FilterForm implements PropertyContainerInterface
{
    /**
     * @var array
     */
    private $propertyContainer = [];

    /**
     * @param $find_date1
     * @param $find_date2
     * @return string
     */
    private function createCalendarPeriod($find_date1, $find_date2)
    {
        return CalendarPeriod(
            "find_date1_stats",
            $find_date1,
            "find_date2_stats",
            $find_date2,
            "form1",
            "Y");
    }

    public function Begin($clFilter = null)
    {
        echo '
<div id="adm-filter-tab-wrap-'.$clFilter->id.'" class="adm-filter-wrap'.($clFilter =="Y" ? " adm-filter-folded" : "").'" style = "display: none;">
	<table class="adm-filter-main-table">
		<tr>
			<td class="adm-filter-main-table-cell">
				<div class="adm-filter-tabs-block" id="filter-tabs-'.$clFilter->id.'">
					<span id="adm-filter-tab-'.$clFilter->id.'-0" class="adm-filter-tab adm-filter-tab-active" onclick="'.$clFilter->id.'.SetActiveTab(this); '.$clFilter->id.'.ApplyFilter(\'0\'); " title="'.GetMessage("admin_lib_filter_goto_dfilter").'">'.GetMessage("admin_lib_filter_filter").'</span>
					<span id="adm-filter-add-tab-'.$clFilter->id.'" class="adm-filter-tab adm-filter-add-tab" onclick="'.$clFilter->id.'.SaveAs();" title="'.GetMessage("admin_lib_filter_new").'"></span>
					<span onclick="'.$clFilter->id.'.SetFoldedView();" class="adm-filter-switcher-tab">
					    <span id="adm-filter-switcher-tab" class="adm-filter-switcher-tab-icon"></span>
					</span>
					<span class="adm-filter-tabs-block-underlay"></span>
				</div>
			</td>
		</tr>
		<tr>
			<td class="adm-filter-main-table-cell">
				<div class="adm-filter-content" id="'.$this->id.'_content">
					<div class="adm-filter-content-table-wrap">
						<table cellspacing="0" class="adm-filter-content-table" id="'.$clFilter->id.'">';
    }

    private function End()
    {

        echo '
                        </table>
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>';
    }
    /**
     *
     */
    public function generateFilterForm()
    {
        global $APPLICATION;
        ?>
        <form name="form1_stats" method="GET" action="<?= $APPLICATION -> GetCurPage() ?>?">
            <?
                $this->Begin();
                $this -> End();
            ?>
        </form>
        <?php
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
}
?>
<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

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

    /**
     *
     */
    public function generateFilterForm()
    {
        global $APPLICATION;
        ?>
        <form name="form1_stats" method="GET" action="<?= $APPLICATION -> GetCurPage() ?>?">
            <?

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
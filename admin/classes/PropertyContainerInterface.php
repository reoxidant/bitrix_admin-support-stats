<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

/**
 * Interface PropertyContainerInterface
 * @package admin\classes
 */
interface PropertyContainerInterface
{
    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    function addProperty($name, $value);

    /**
     * @param $name
     * @return mixed
     */
    function getProperty($name);
}

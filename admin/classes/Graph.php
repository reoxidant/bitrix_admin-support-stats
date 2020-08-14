<?php
/**
 * Description actions
 * @copyright 2020 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

namespace admin\classes;

require_once('PropertyContainerInterface.php');

class Graph implements PropertyContainerInterface
{
    private $propertyContainer = [];

    public function addProperty($name, $value)
    {
        $this->propertyContainer[$name] = $value;
    }

    public function getProperty($name)
    {
        return $this->propertyContainer[$name] ?? null;
    }

    public function getAllProperties()
    {
        return $this->propertyContainer ?? null;
    }
}
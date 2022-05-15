<?php

namespace Controllers;

use \Models\Admin;

/**
 * Class MainPageController
 * @package Controllers
 */
class MainPageController extends BaseController
{
    /**
     * Render main page
     */
    public function render()
    {
        $name = Admin::get()->getName();
        include ROOT . "/views/main.phtml";
    }
}
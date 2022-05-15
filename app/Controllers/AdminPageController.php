<?php

namespace Controllers;

use \Models\Admin;

/**
 * Class AdminPageController
 * @package Controllers
 */
class AdminPageController extends BaseController
{
    /**
     * render admin page
     */
    public function render()
    {
        $name = Admin::get()->getName();
        include ROOT . "/views/admin.phtml";
    }
}
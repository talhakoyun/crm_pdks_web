<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\MenuRequest;
use App\Models\Menu;
use App\Models\Route;

class MenuController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->title = 'Menü';
        $this->page = 'menu';
        $this->upload = 'menu';
        $this->model = new Menu();
        $this->request = new MenuRequest();
        $this->view = (object)array(
            'breadcrumb' => array(
                'Menüler' => route('backend.menu_list'),
            ),
        );
        view()->share('categories', Menu::category()->get());
        view()->share('routes', Route::active()->get());
        parent::__construct();
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\SettingRequest;
use App\Models\Setting;

class SettingController extends BaseController
{
    use BasePattern;

    public function __construct() {
        $this->title = 'Ayarlar';
        $this->page = 'setting';
        $this->upload = 'setting';
        $this->model = new Setting();
        $this->request = new SettingRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Ayarlar' => route('backend.setting_list'),
            ),
        );
        parent::__construct();
    }
}


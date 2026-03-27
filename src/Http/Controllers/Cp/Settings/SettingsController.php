<?php

namespace Cartino\Http\Controllers\Cp\Settings;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $page = Page::make(__('settings.title'))->breadcrumb('Home', '/cp')->breadcrumb(__('settings.title'));

        return Inertia::render('settings/index', compact('page'));
    }
}

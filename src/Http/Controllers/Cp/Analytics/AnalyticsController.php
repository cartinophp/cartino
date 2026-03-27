<?php

namespace Cartino\Http\Controllers\Cp\Analytics;

use Cartino\Cp\Page;
use Cartino\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        $page = Page::make(__('analytics.title'))->breadcrumb('Home', '/cp')->breadcrumb(__('analytics.title'));

        return Inertia::render('analytics/index', compact('page'));
    }
}

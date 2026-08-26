<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Marketplace\Models\Comment;
use Azuriom\Plugin\Marketplace\Models\Report;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportController extends Controller
{
    public function index()
    {
        return view('marketplace::admin.reports.index', [
            'reports' => Report::with([
                'reporter',
                'reportable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Comment::class => ['resource', 'user'],
                ]),
            ])
                ->latest()
                ->paginate(25),
        ]);
    }
}

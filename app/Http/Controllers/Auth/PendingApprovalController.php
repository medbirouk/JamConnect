<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PendingApprovalController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Auth/PendingApproval');
    }
}

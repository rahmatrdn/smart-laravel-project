<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Usecases\GuestUsecase;
use App\Usecases\LoanUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
    ) {
    }

    public function index(): View
    {
        // dd(session('access_type'));

        return render_view("_admin.dashboard");
    }
}

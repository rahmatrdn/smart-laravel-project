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

        return render_view("_admin.dashboard");
    }
}

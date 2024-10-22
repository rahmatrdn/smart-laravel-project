<?php

namespace App\Http\Controllers\Admin;

use App\Entities\Database;
use App\Http\Controllers\Controller;
use App\Http\Entities\CompanyEntity;
use App\Http\Entities\GeneralEntity;
use App\Http\Usecases\UserUsecase;
use Dflydev\DotAccessData\Data;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(): View
    {
        return view("_admin.auth.login");
    }

    public function doLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required'],
            'password' => ['required'],
        ]);

        $request->session()->invalidate();

        if (Auth::attempt($credentials)) {
            $accessType = Auth::user()->access_type;
            session(['access_type' => $accessType]);

            $request->session()->regenerate();

            return redirect()->intended('admin')->with('success', "Selamat Datang kembali!");
        } else {
            return redirect('admin/auth/login')->withError("Email/Password salah, periksa kembali dan coba lagi!");
        }
    }

    public function doLogout(): RedirectResponse
    {
        Auth::logout();

        return redirect("admin/auth/login");
    }

    public function changePassword(): View
    {
        return view("admin.auth.change-password");
    }

    // public function doChangePassword(Request $request): RedirectResponse
    // {
    //     $usecase = new UserUsecase();
    //     $createProcess = $usecase->changePassword(
    //         data: $request->all(),
    //         userID: $request->user()->id,
    //         isAPI: false
    //     );

    //     if (empty($createProcess['error'])) {
    //         return redirect()
    //             ->intended('company/setting')
    //             ->with('success', GeneralEntity::SUCCESS_MESSAGE_UPDATED);
    //     } else {
    //         return redirect()
    //             ->intended('company/auth/change-password')
    //             ->with('error', $createProcess['message']);
    //     }
    // }
}

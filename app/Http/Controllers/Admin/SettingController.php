<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\MemberCategoryUsecase;
use App\Usecases\UserUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SettingController extends Controller
{
    protected $usecase;
    protected $userUsecase;
    protected $page = [
        "route" => "setting",
        "title" => "Pengaturan",
    ];
    protected $baseRedirect;

    public function __construct(
        MemberCategoryUsecase $usecase,
        UserUsecase $userUsecase,
    ) {
        $this->usecase = $usecase;
        $this->userUsecase = $userUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function general(Request $req): View | Response
    {
        $theme = session("theme") ?? "light";

        return render_view("_admin.setting.general", [
            'theme' => $theme,
            'page' => $this->page,
        ]);
    }

    public function doUpdateGeneral(Request $request): RedirectResponse
    {
        $theme = $request->input('theme', 'light');

        session(['theme' => $theme]);

        return redirect()
            ->intended("admin/setting/general")
            ->with('success', ResponseEntity::SUCCESS_MESSAGE_UPDATED);
    }

    public function doUpdate(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->update(
            data: $request,
            id: $id,
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_UPDATED,
                "redirect" => "member-category"
            ]);
        } else {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "member-category"
            ]);
        }
    }

    public function doDelete(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->delete(
            id: $id,
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_DELETED,
                "redirect" => "member-category"
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "member-category"
            ]);
        }
    }

    public function changePassword(): View | Response
    {
        return render_view("_admin.setting.change-password", [
            'page' => $this->page,
        ]);
    }

    // Method untuk memproses perubahan password
    public function doChangePassword(Request $request): JsonResponse
    {
        $process = $this->userUsecase->changePassword(
            data: $request->input(),
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => "Password berhasil diubah!",
                "redirect" => "setting/change-password"
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "setting/change-password"
            ]);
        }
    }
}

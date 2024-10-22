<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Entities\ResponseEntity;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Usecases\UserUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $usecase;
    protected $page = [
        "route" => "user",
        "title" => "Pengguna Aplikasi",
    ];
    protected $baseRedirect;

    public function __construct(UserUsecase $usecase)
    {
        $this->usecase = $usecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(): View
    {
        $data = $this->usecase->getAll();

        return render_view("_admin.users.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
        ]);
    }

    public function add(): View
    {
        return render_view("_admin.users.add", [
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $request): JsonResponse
    {
        $process = $this->usecase->create(
            data: $request,
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_CREATED,
                "redirect" => $this->page['route']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => $this->page['route']
            ]);
        }
    }

    public function update(int $id): View|RedirectResponse
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        return render_view("_admin.users.update", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
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
                "redirect" => $this->page['route']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => $this->page['route']
            ]);
        }
    }

    public function doDelete(int $id): JsonResponse
    {
        $process = $this->usecase->delete(id: $id);

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_DELETED,
                "redirect" => $this->page['route']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => $this->page['route']
            ]);
        }
    }

    public function changePassword(): View
    {
        return render_view("_admin.users.change-password", [
            'page' => $this->page,
        ]);
    }

    // Method untuk memproses perubahan password
    public function doChangePassword(Request $request): JsonResponse
    {
        $process = $this->usecase->changePassword(
            data: $request->input(),
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => "Password berhasil diubah!",
                "redirect" => "user/change-password"
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "user/change-password"
            ]);
        }
    }

    public function resetPassword(int $id): JsonResponse
    {
        $resetProcess = $this->usecase->resetPassword(id: $id);

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => 'Password berhasil direset menjadi default',
                "redirect" => $this->page['route']
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => $this->page['route']
            ]);
        }
    }
}

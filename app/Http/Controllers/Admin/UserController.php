<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Entities\ResponseEntity;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Usecases\UserUsecase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $usecase;
    protected $page = [
        "route" => "users",
        "title" => "Data Pengguna",
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

        return view("_admin.users.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
        ]);
    }

    public function add(): View
    {
        return view("_admin.users.add", [
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->create(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('success', $createProcess['message']);
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
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

        return view("_admin.users.update", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }

    public function doUpdate(int $id, Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->update(
            data: $request,
            id: $id,
        );

        if (empty($createProcess['error'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('success', ResponseEntity::SUCCESS_MESSAGE_UPDATED);
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function doDelete(int $id): RedirectResponse
    {
        $createProcess = $this->usecase->delete(id: $id);

        if (empty($createProcess['error'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('success', ResponseEntity::SUCCESS_MESSAGE_DELETED);
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function changePassword(): View
    {

        return view("_admin.users.change-password", [
            'page' => $this->page,
        ]);
    }

    // Method untuk memproses perubahan password
    public function doChangePassword(Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->changePassword(
            data: $request->input(),
        );

        if (empty($createProcess['error'])) {
            return redirect()
                ->intended("admin/users/change-password")
                ->with('success', "Password berhasil diubah!");
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }
    public function resetPassword(int $id): RedirectResponse
    {
        // Panggil usecase untuk reset password
        $resetProcess = $this->usecase->resetPassword(id: $id);

        // Cek jika berhasil
        if (empty($resetProcess['error'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('success', 'Password berhasil direset menjadi default.');
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }


}

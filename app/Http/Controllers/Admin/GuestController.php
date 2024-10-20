<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookCategoryUsecase;
use App\Usecases\GuestUsecase;
use App\Usecases\MemberUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class GuestController extends Controller
{
    protected $usecase;
    protected $memberUsecase;
    protected $page = [
        "route" => "guest",
        "title" => "Buku Tamu",
    ];
    protected $baseRedirect;

    public function __construct(GuestUsecase $usecase, MemberUsecase $memberUsecase)
    {
        $this->usecase = $usecase;
        $this->memberUsecase = $memberUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(): View
    {
        $data = $this->usecase->getAll();

        return view("_admin.guest.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $req): RedirectResponse
    {
        $member = $this->memberUsecase->getByIdentityNo($req->input('identity_no'));
        $member = $member['data'] ?? [];

        if (count($member)) {
            $this->usecase->create(
                memberID: $member['id'],
            );

            return redirect("admin/guest")
                ->with('success', "Pengunjung berhasil ditambahkan!");
        } else {
            return redirect("admin/guest")
                ->with('error', "Anggota tidak ditemukan!");
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookUsecase;
use App\Usecases\LoanUsecase;
use App\Usecases\MemberUsecase;
use App\Usecases\ReturnUsecase;
use App\Usecases\SettingUsecase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    protected $usecase;
    protected $returnUsecase;
    protected $memberUsecase;
    protected $bookUsecase;
    protected $settingUsecase;
    protected $page = [
        "route" => "return",
        "title" => "Pengembalian Buku",
    ];
    protected $baseRedirect;

    public function __construct(
        LoanUsecase $usecase,
        ReturnUsecase $returnUsecase,
        MemberUsecase $memberUsecase,
        BookUsecase $bookUsecase,
        SettingUsecase $settingUsecase,
    ) {
        $this->usecase = $usecase;
        $this->returnUsecase = $returnUsecase;
        $this->memberUsecase = $memberUsecase;
        $this->bookUsecase = $bookUsecase;
        $this->settingUsecase = $settingUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function return(Request $req): View | RedirectResponse
    {
        $loanID = (int) $req->input('loan_id');

        if (empty($loanID)) {
            return view("_admin.transaction.return.return", [
                'data' => [],
                'page' => $this->page,
            ]);
        }

        $memberID = $req->input("member_id") ?? "";
        
        $data = $this->usecase->getByID($loanID);
        $data = $data['data'] ?? [];

        if (!count($data)) {
            return redirect("admin/return")
                ->with('error', "Data Peminjaman tidak ditemukan!");
        }

        $details = $this->usecase->getDetailsByID($loanID);
        $details = $details['data'] ?? [];

        $member = $this->memberUsecase->getByID($data['member_id']);
        $member = $member['data'] ?? [];

        $setting = $this->settingUsecase->get();
        $setting = $setting['data'] ?? [];

        return view("_admin.transaction.return.return", [
            'data' => (object) $data,
            'details' => $details,
            'member' => $member,
            'memberID' => $memberID,
            'page' => $this->page,
            'setting' => $setting
        ]);
    }

    public function doReturn(Request $request): RedirectResponse
    {
        $createProcess = $this->returnUsecase->return(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            return redirect()
                ->intended('admin/loan/detail/' . $createProcess['data']['loan_id'])
                ->with('success', "Pengembalian Buku Berhasil!");
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function returnWithMemberID(Request $req): View | RedirectResponse
    {
        $identityNo = (string) $req->input('identity_no');

        if (empty($identityNo)) {
            return view("_admin.transaction.return.return-2", [
                'data' => [],
                'page' => $this->page,
            ]);
        }

        $member = $this->memberUsecase->getByIdentityNo($identityNo);
        $member = $member['data'] ?? [];
        if (!count($member)) {
            return redirect("admin/return-2")
                ->with('error', "Data Anggota tidak ditemukan!");
        }
        
        return redirect("admin/member/detail/" . $member['id']);
    }
}

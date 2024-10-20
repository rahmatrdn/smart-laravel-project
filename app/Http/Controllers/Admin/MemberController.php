<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookCategoryUsecase;
use App\Usecases\BookshelveUsecase;
use App\Usecases\BookUsecase;
use App\Usecases\LoanUsecase;
use App\Usecases\MemberCategoryUsecase;
use App\Usecases\MemberUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MemberController extends Controller
{
    protected $usecase;
    protected $memberCategoryUsecase;
    protected $bookshelveUsecase;
    protected $loanUsecase;
    protected $page = [
        "route" => "member",
        "title" => "Anggota",
    ];
    protected $baseRedirect;

    public function __construct(
        MemberUsecase $usecase,
        MemberCategoryUsecase $memberCategoryUsecase,
        LoanUsecase $loanUsecase,
    ) {
        $this->usecase = $usecase;
        $this->memberCategoryUsecase = $memberCategoryUsecase;
        $this->loanUsecase = $loanUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View
    {
        $data = $this->usecase->getAll($req->input());
        
        $memberCategories = $this->memberCategoryUsecase->getAll();
        $memberCategories = $memberCategories['data']['list'] ?? [];

        return render_view("_admin.member.list", [
            'data' => $data['data']['list'] ?? [],
            'memberCategories' => $memberCategories,
            'page' => $this->page,
            'filter' => $req->input(),
        ]);
    }

    public function add(): View
    {
        $memberCategories = $this->memberCategoryUsecase->getAll();
        $memberCategories = $memberCategories['data']['list'] ?? [];

        return render_view("_admin.member.add", [
            'page' => $this->page,
            'memberCategories' => $memberCategories,
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

        $memberCategories = $this->memberCategoryUsecase->getAll();
        $memberCategories = $memberCategories['data']['list'] ?? [];

        return render_view("_admin.member.update", [
            'data' => (object) $data,
            'page' => $this->page,
            'memberCategories' => $memberCategories,
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

    public function doDelete(int $id, Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->delete(
            id: $id,
        );

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

    public function searchAPI(Request $req): JsonResponse
    {
        $data = $this->usecase->getByKeywordName($req->input());
        $data = $data['data']['list'] ?? [];

        if (!count($data)) {
            return response()->json([]);
        }

        foreach ($data as $row) {
            $result[] = array(
                'id'          => $row->id,
                'name'        => $row->name,
                'identity_no' => $row->identity_no,
                'identity_type' => $row->identity_type,
            );
        }

        return response()->json($result);
    }

    public function import(): View
    {
        return render_view("_admin.member.import", [
            'page' => $this->page,
        ]);
    }

    public function doReview(Request $req): View | RedirectResponse
    {
        $data = $this->usecase->getExcelFile($req);

        if (empty($data['error'])) {
            return render_view("_admin.member.import-review", [
                'page' => $this->page,
                'data' => $data
            ]);
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function doImportInsert(Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->importProcess(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('success', "Import Data Berhasil!");
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function detail(int $id): View|RedirectResponse
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        $loans = $this->loanUsecase->getAllByMemberID($id);
        $loans = $loans['data'] ?? [];

        return render_view("_admin.member.detail", [
            'data' => (object) $data,
            'page' => $this->page,
            'loans' => $loans,
        ]);
    }
}

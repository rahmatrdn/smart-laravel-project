<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookCategoryUsecase;
use App\Usecases\BookshelveUsecase;
use App\Usecases\BookUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class BookController extends Controller
{
    protected $usecase;
    protected $bookCategoryUsecase;
    protected $bookshelveUsecase;
    protected $page = [
        "route" => "book",
        "title" => "Buku",
    ];
    protected $baseRedirect;

    public function __construct(
        BookUsecase $usecase,
        BookCategoryUsecase $bookCategoryUsecase,
        BookshelveUsecase $bookshelveUsecase
        )
    {
        $this->usecase = $usecase;
        $this->bookCategoryUsecase = $bookCategoryUsecase;
        $this->bookshelveUsecase = $bookshelveUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View
    {
        $data = $this->usecase->getAll($req->input());
        $bookCategories = $this->bookCategoryUsecase->getAll();
        $bookCategories = $bookCategories['data']['list'] ?? [];

        $bookshelves = $this->bookshelveUsecase->getAll();
        $bookshelves = $bookshelves['data']['list'] ?? [];

        return view("_admin.book.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
            'bookCategories' => $bookCategories,
            'bookshelves' => $bookshelves,
            'filter' => $req->input(),
        ]);
    }

    public function add(): View
    {
        $bookCategories = $this->bookCategoryUsecase->getAll();
        $bookCategories = $bookCategories['data']['list'] ?? [];

        $bookshelves = $this->bookshelveUsecase->getAll();
        $bookshelves = $bookshelves['data']['list'] ?? [];

        return view("_admin.book.add", [
            'page' => $this->page,
            'bookCategories' => $bookCategories,
            'bookshelves' => $bookshelves,
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
        $bookCategories = $this->bookCategoryUsecase->getAll();
        $bookCategories = $bookCategories['data']['list'] ?? [];

        $bookshelves = $this->bookshelveUsecase->getAll();
        $bookshelves = $bookshelves['data']['list'] ?? [];

        return view("_admin.book.update", [
            'data' => (object) $data,
            'page' => $this->page,
            'bookCategories' => $bookCategories,
            'bookshelves' => $bookshelves,
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

    public function detail(int $id): View|RedirectResponse
    {
        $data = $this->usecase->getDetailByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        $stocks = $this->usecase->getStocksByID($id);
        $stocks = $stocks['data'] ?? [];

        return view("_admin.book.detail", [
            'data' => (object) $data,
            'page' => $this->page,
            'stocks' => $stocks
        ]);
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
                'book_id'     => $row->id,
                'name'        => $row->name,
            );
        }

        return response()->json($result);
    }


    public function import(): View
    {
        return view("_admin.book.import", [
            'page' => $this->page,
        ]);
    }

    public function doReview(Request $req): View | RedirectResponse
    {
        $data = $this->usecase->getExcelFile($req);

        if (empty($data['error'])) {
            return view("_admin.book.import-review", [
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
}

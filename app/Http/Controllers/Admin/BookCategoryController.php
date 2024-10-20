<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookCategoryUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


class BookCategoryController extends Controller
{
    protected $usecase;
    protected $page = [
        "route" => "book-category",
        "title" => "Kategori Buku",
    ];
    protected $baseRedirect;

    public function __construct(BookCategoryUsecase $usecase)
    {
        $this->usecase = $usecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(): View
    {
        $data = $this->usecase->getAll();

        return view("_admin.book-category.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
        ]);
    }
    public function indexV2(Request $request): View|string
    {
        $data = $this->usecase->getAll($request->input());

        if ($request->ajax()) {
            return view('_admin.book-category/table-data', [
                'data' => $data['data']['list'] ?? [],
                'page' => $this->page,
            ])->render();
        }

        return view("_admin.book-category.list-v2", [
            'page' => $this->page,
        ]);
    }

    public function add(): View
    {
        return view("_admin.book-category.add", [
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

        return view("_admin.book-category.update", [
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
}

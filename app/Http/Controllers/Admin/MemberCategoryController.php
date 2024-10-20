<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\MemberCategoryUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberCategoryController extends Controller
{
    protected $usecase;
    protected $page = [
        "route" => "member-category",
        "title" => "Kategori Anggota",
    ];
    protected $baseRedirect;

    public function __construct(MemberCategoryUsecase $usecase)
    {
        $this->usecase = $usecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View
    {
        $data = $this->usecase->getAll();

        return render_view("_admin.member-category.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
        ]);
    }

    public function add(): View
    {
        return render_view("_admin.member-category.add", [
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $request): JsonResponse
    {
        $createProcess = $this->usecase->create(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            // return redirect()
            //     ->intended($this->baseRedirect)
            //     ->with('success', $createProcess['message']);

            return response()->json([
                "success" => true, 
                "message" => $createProcess['message'],
                "redirect" => "member-category"
            ]);
        } else {
            // return redirect()
            //     ->intended($this->baseRedirect)
            //     ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE)
            
            return response()->json([
                "success" => false, 
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => $this->baseRedirect
            ]);;
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

        return render_view("_admin.member-category.update", [
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
                "success" => true, 
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "member-category"
            ]);
        }
    }
}

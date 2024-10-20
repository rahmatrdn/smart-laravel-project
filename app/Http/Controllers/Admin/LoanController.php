<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookUsecase;
use App\Usecases\LoanUsecase;
use App\Usecases\MemberCategoryUsecase;
use App\Usecases\MemberUsecase;
use App\Usecases\SettingUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class LoanController extends Controller
{
    protected $usecase;
    protected $memberUsecase;
    protected $bookUsecase;
    protected $settingUsecase;
    protected $page = [
        "route" => "loan",
        "title" => "Peminjaman Buku",
    ];
    protected $baseRedirect;

    public function __construct(
        LoanUsecase $usecase,
        MemberUsecase $memberUsecase,
        BookUsecase $bookUsecase,
        SettingUsecase $settingUsecase,
    ) {
        $this->usecase = $usecase;
        $this->memberUsecase = $memberUsecase;
        $this->bookUsecase = $bookUsecase;
        $this->settingUsecase = $settingUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View
    {
        $req = $req->input();
        $data = $this->usecase->getAll($req);

        return view("_admin.transaction.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
            'filter' => $req
        ]);
    }
    public function indexV2(Request $req): View|string
    {
        $reqInput = $req->input();
        $data = $this->usecase->getAll($reqInput);
        
        if ($req->ajax()) {
            return view("_admin.transaction.table-data", [
                'data' => $data['data']['list'] ?? [],
                'page' => $this->page,
            ]);
        }

        return view("_admin.transaction.list-v2", [
            'page' => $this->page,
            'filter' => $reqInput
        ]);
    }

    public function add(Request $req): View | RedirectResponse
    {
        $member = [];
        if ($req->input('identity_no')) {
            $member = $this->memberUsecase->getByIdentityNo($req->input('identity_no'));
            $member = $member['data'] ?? [];

            if (!empty($member)) {
                $this->clearCart();
                return redirect("admin/loan/add-2?identity_no=" . $req->input('identity_no'));
            } else {
                return redirect("admin/loan/add")
                    ->with('error', "Anggota tidak ditemukan!");
            }
        }

        return view("_admin.transaction.loan.add", [
            'page' => $this->page,
            'member' => $member
        ]);
    }

    public function addStepTwo(Request $req): View | RedirectResponse
    {
        $member = [];
        if ($req->input('identity_no')) {
            $member = $this->memberUsecase->getByIdentityNo($req->input('identity_no'));
            $member = $member['data'] ?? [];

            if (empty($member)) {
                return redirect("admin/loan/add")
                    ->with('error', "Anggota tidak ditemukan!");
            }

            if ($member['category_id'] == 2) {
                $hasLoan = $this->usecase->getActiveByMemberID($member['id']);

                if (count($hasLoan['data']) > 3) {
                    return redirect("admin/loan/add")
                        ->with('error', "<b>{$member['name']}</b> memiliki 3 tanggungan peminjaman!");
                }
            }
        }

        $setting = $this->settingUsecase->get();
        $setting = $setting['data'] ?? [];

        return view("_admin.transaction.loan.add-2", [
            'page'    => $this->page,
            'member'  => $member,
            'setting' => $setting,
        ]);
    }

    public function review(Request $req): View
    {
        $cart = collect(session()->get('cart', []));
        $sortedcart = $cart->reverse()->values();

        return view("_admin.transaction.loan.review", [
            'page' => $this->page,
            'req' => $req->input(),
            'carts' => $sortedcart
        ]);
    }

    public function detail($id): View | RedirectResponse
    {
        $data = $this->usecase->getByID($id);
        $data = $data['data'] ?? [];

        if (!count($data)) {
            return redirect("admin/loan")
                ->with('error', "Data Peminjaman tidak ditemukan!");
        }

        $details = $this->usecase->getDetailsByID($id);
        $details = $details['data'] ?? [];

        $member = $this->memberUsecase->getByID($data['member_id']);
        $member = $member['data'] ?? [];

        return view("_admin.transaction.loan.detail", [
            'data' => (object) $data,
            'details' => $details,
            'member' => $member,
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->create(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            $this->clearCart();

            return redirect()
                ->intended($this->baseRedirect . '/detail/' . $createProcess['data']['loan_id'])
                ->with('success', "Peminjaman Berhasil dibuat!");
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }

    public function doCancel(Request $request): RedirectResponse
    {
        $createProcess = $this->usecase->cancel(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            $this->clearCart();

            return redirect()
                ->intended($this->baseRedirect . '/detail/' . $createProcess['data']['loan_id'])
                ->with('success', "Peminjaman Berhasil dibatalkan!");
        } else {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
    }


    // ======================

    public function addToCart(Request $request): JsonResponse
    {
        $cart = session()->get('cart', []);

        $id = $request->input('book_id');
        $book = $this->bookUsecase->getByID((int) $id);
        $book = $book['data'] ?? [];

        if (empty($book)) {
            return response()->json([
                'success' => false,
                'message' => "Buku tidak ditemukan!"
            ]);
        }

        $name = $book['name'];
        $quantity = $request->input('quantity', 1);

        $item = [
            'book_id' => $id,
            'name' => "[$id] " . $name,
            'quantity' => $quantity,
        ];

        if (empty($cart)) {
            if ($quantity > $book['total_stock']) {
                return response()->json([
                    'success' => false,
                    'message' => "Ada masalah pada stok, silahkan sesuaikan terlebih dahulu stok buku " . $book['name']
                ]);
            }
        }

        $found = false;
        foreach ($cart as &$cartItem) {
            if ($cartItem['book_id'] == $id) {
                $cartItem['quantity'] += $quantity;

                if ($cartItem['quantity'] > $book['total_stock']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Ada masalah pada stok, silahkan sesuaikan terlebih dahulu stok buku " . $book['name']
                    ]);
                }

                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart[] = $item;
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'cart' => $cart,
        ]);
    }

    public function getCarts(): JsonResponse
    {
        $data = collect(session()->get('cart', []));
        $sortedData = $data->reverse()->values();

        return response()->json($sortedData);
    }

    public function deleteCartByID($id): JsonResponse
    {
        $cart = session()->get('cart', []);

        // Temukan index item berdasarkan book_id
        foreach ($cart as $key => $cartItem) {
            if ($cartItem['book_id'] == $id) {
                unset($cart[$key]);
                break;
            }
        }

        session()->put('cart', array_values($cart));

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => $cart,
        ]);
    }

    public function clearCart(): void
    {
        session()->forget('cart');
    }
}

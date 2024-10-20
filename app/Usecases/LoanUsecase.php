<?php

namespace App\Usecases;

use App\Entities\BookEntity;
use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

const STATUS_DALAM_PEMINJAMAN = 1;
const STATUS_DIBATALKAN = 3;

class LoanUsecase extends Usecase
{
    public string $className;
    public $guestUsecase;

    public function __construct(GuestUsecase $guestUsecase)
    {
        $this->className = "LoanUsecase";
        $this->guestUsecase = $guestUsecase;
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $page       = $filterData['page'] ?? 1;
        $limit      = $filterData['limit'] ?? 10;
        $page       = ($page > 0 ? $page : 1);
        $filterStatus = $filterData['filter_status'] ?? "";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN, 'x')
                ->leftJoin("members as m", "m.id", "=", "x.member_id")
                ->whereNull("x.deleted_at");

            if (!empty($filterStatus)) {
                if ($filterStatus == 100) {
                    $data = $data->where('x.status', 1)
                        ->where(DB::raw('NOW()'), '>', 'x.end_date')
                        ->where(DB::raw('DATE(x.end_date)'), '<', DB::raw('CURDATE()'));
                } else {
                    $data = $data->where('x.status', $filterStatus);
                }
            }

            $fields = ['x.*', 'm.name as member_name', 'm.identity_no', 'm.identity_type', 'm.category_id'];

            $data = $data->orderBy("x.created_at", "desc")->paginate(10, $fields)->appends(request()->query());

            return Response::buildSuccess(
                [
                    'list' => $data,
                    'pagination' => [
                        'current_page' => (int) $page,
                        'limit'        => (int) $limit,
                        'payload'      => $filterData
                    ]
                ],
                ResponseEntity::HTTP_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN, 'x')
                ->leftJoin("users as u", "u.id", "=", "x.created_by")
                ->whereNull("x.deleted_at")
                ->where('x.id', $id)
                ->first(['x.*', 'u.name as user_name']);

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getBookDetailByID(int $id): array
    {
        $funcName = $this->className . ".getBookDetailByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN_DETAIL, 'x')
                ->whereNull("deleted_at")
                ->where('loan_id', $id)
                ->get();

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getActiveByMemberID(int $id): array
    {
        $funcName = $this->className . ".getActiveByMemberID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN, 'x')
                ->whereNull("deleted_at")
                ->where('member_id', $id)
                ->where('status', 1)
                ->get();

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getAllByMemberID(int $id, bool $isExceptActive = false): array
    {
        $funcName = $this->className . ".getAllByMemberID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN, 'x')
                ->whereNull("deleted_at")
                ->where('member_id', $id);

            if ($isExceptActive) {
                $data = $data->where('status', "!=", 1);
            }

            $data = $data->orderby("id", 'desc')->limit(20)->get();

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getDetailsByID(int $id): array
    {
        $funcName = $this->className . ".getDetailsByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN_DETAIL)
                ->where('loan_id', $id)
                ->get();

            return Response::buildSuccess(
                data: collect($data)->toArray()
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function create(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'member_id' => 'required',
        ]);

        $customAttributes = [
            'member_id' => 'Anggota',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();
        try {
            $carts = session()->get('cart', []);
            $cartCollection = collect($carts);

            if (!$cartCollection->count()) {
                throw new Exception("EMPTY CARTS");
            }

            $loanID = DB::table(DatabaseEntity::LOAN)
                ->insertGetId([
                    'member_id'  => $data['member_id'],
                    'total_days' => $data['total_days'],
                    'total_qty'  => $cartCollection->sum('quantity'),
                    'start_date' => date_now(),
                    'end_date'   => $data['end_date'],
                    'total_days' => $data['total_days'],
                    'note'       => $data['note'],
                    'status'     => STATUS_DALAM_PEMINJAMAN,
                    'created_by' => Auth::user()->id,
                    'created_at' => datetime_now()
                ]);


            foreach ($carts as $c) {
                $qty = (int) $c['quantity'];
                $bookID = (int) $c['book_id'];

                $book = DB::table(DatabaseEntity::BOOK)->where('id', $bookID)->first();

                DB::table(DatabaseEntity::LOAN_DETAIL)
                    ->insert([
                        'loan_id'   => $loanID,
                        'book_id'   => $bookID,
                        'book_name' => $book->name,
                        'qty'       => $qty,
                    ]);

                DB::table(DatabaseEntity::BOOK_STOCK)
                    ->insert([
                        'book_id'      => $bookID,
                        'description'  => "Peminjaman Buku",
                        'reference_id' => $loanID,
                        'is_loan'      => 1,
                        'type'         => BookEntity::STOCK_OUT,
                        'quantity'     => $qty,
                        'created_by'   => Auth::user()->id,
                        'created_at'   => datetime_now()
                    ]);

                DB::table(DatabaseEntity::BOOK)
                    ->where('id', $bookID)
                    ->update([
                        'total_stock' => $book->total_stock - $qty,
                    ]);
            }
            
            $this->guestUsecase->create($data['member_id']);

            DB::commit();

            return Response::buildSuccessCreated([
                'loan_id' => $loanID
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function update(Request $data, int $id): array
    {
        $return = [];
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'name' => 'required',
        ]);

        $customAttributes = [
            'name' => 'Nama Kategori',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        $update = [
            'name'       => $data['name'],
            'updated_by' => Auth::user()->id,
            'updated_at' => datetime_now()
        ];

        DB::beginTransaction();

        try {
            DB::table(DatabaseEntity::MEMBER_CATEGORY)
                ->where("id", $id)
                ->update($update);

            DB::commit();

            $return = Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }

        return $return;
    }

    public function delete(int $id): array
    {
        $return = [];
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {
            $delete = DB::table(DatabaseEntity::MEMBER_CATEGORY)
                ->where('id', $id)
                ->update([
                    'deleted_by' => Auth::user()->id,
                    'deleted_at' => datetime_now(),
                ]);

            if (!$delete) {
                DB::rollback();

                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();

            $return = Response::buildSuccess();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }

        return $return;
    }

    public function cancel(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'loan_id' => 'required',
        ]);

        $customAttributes = [
            'loan_id' => 'ID Peminjaman',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        $loanID = (int) $data['loan_id'];

        $update = [
            'status'     => STATUS_DIBATALKAN,
            'updated_by' => Auth::user()->id,
            'updated_at' => datetime_now()
        ];

        $loan = DB::table(DatabaseEntity::LOAN)->where("id", $loanID)->first();
        if ($loan->status == STATUS_DIBATALKAN) {
            return Response::buildSuccessCreated([
                'loan_id' => $loanID
            ]);
        }

        DB::beginTransaction();
        try {
            DB::table(DatabaseEntity::LOAN)
                ->where("id", $loanID)
                ->update($update);

            $loanDetails = DB::table(DatabaseEntity::LOAN_DETAIL)->where('loan_id', $loanID)->get();

            foreach ($loanDetails as $c) {
                $qty = (int) $c->qty;
                $bookID = (int) $c->book_id;

                DB::table(DatabaseEntity::BOOK_STOCK)
                    ->insert([
                        'book_id'      => $bookID,
                        'description'  => "Pengembalian Buku",
                        'reference_id' => $loanID,
                        'is_loan'      => 1,
                        'type'         => 1,
                        'quantity'     => $qty,
                        'created_by'   => Auth::user()->id,
                        'created_at'   => datetime_now()
                    ]);

                $book = DB::table(DatabaseEntity::BOOK)->where('id', $bookID)->first();

                DB::table(DatabaseEntity::BOOK)
                    ->where('id', $bookID)
                    ->update([
                        'total_stock' => $book->total_stock + $qty,
                    ]);
            }

            DB::commit();

            return Response::buildSuccessCreated([
                'loan_id' => $loanID
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getMemberLate(): array
    {
        $funcName = $this->className . ".getMemberLate";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->select(
                    'SELECT
                    member_id,
                    m.name,
                    m.identity_no,
                    m.identity_type,
                    m.join_year,
                    m.category_id,
                    DATEDIFF(NOW(), end_date) AS days_late
                FROM loans l
                left join members m
                    on m.id = l.member_id
                WHERE status = 1
                AND NOW() > end_date
                AND DATE(end_date) < CURDATE()
                ORDER BY days_late DESC
                LIMIT 10;'
                );
            $res = collect($data)->map(function ($item) {
                $item->name = title($item->name);

                return $item;
            });

            return Response::buildSuccess(
                data: $res->toArray()
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getCountLast7Day(): array
    {
        $funcName = $this->className . ".getMemberLate";

        try {
            $loans = DB::select("SELECT 
                    DATE(start_date) as date, COUNT(id) as total_loans
                FROM loans
                WHERE start_date >= CURDATE() - INTERVAL 9 DAY -- Ambil 9 hari terakhir untuk mengakomodasi weekend
                AND WEEKDAY(start_date) < 5 -- Mengecualikan Sabtu (5) dan Minggu (6)
                GROUP BY DATE(start_date)
            ");
            $startDate = Carbon::now()->subDays(9); // Mengambil 9 hari terakhir untuk mengantisipasi weekend
            $endDate = Carbon::now();

            $days = collect();
            $workDaysCount = 0;

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                // Cek apakah hari ini adalah hari Sabtu (6) atau Minggu (0)
                if ($date->isWeekend()) {
                    continue; // Jika hari Sabtu atau Minggu, lewati
                }

                $days->put($date->format('Y-m-d'), 0);
                $workDaysCount++;

                // Hentikan setelah 7 hari kerja
                if ($workDaysCount >= 7) {
                    break;
                }
            }

            // Ubah hasil query menjadi collection
            $loanCollection = collect($loans);

            // Isi data peminjaman dari hasil query ke dalam Collection
            $loanCollection->each(function ($loan) use ($days) {
                $days->put($loan->date, $loan->total_loans);
            });

            // Hasil akhir akan menampilkan jumlah peminjaman per hari kerja dengan nilai 0 jika tidak ada peminjaman
            $results = $days->map(function ($totalLoans, $date) {
                return [
                    'date' => $date,
                    'total' => $totalLoans,
                ];
            });

            return Response::buildSuccess(
                data: $results->values()->toArray(),
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getCountToday(): array
    {
        $funcName = $this->className . ".getCountToday";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::LOAN)
                ->where('start_date', date_now())
                ->count();

            return Response::buildSuccess(
                data: [
                    'count' => $data
                ],
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }
}

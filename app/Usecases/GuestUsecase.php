<?php

namespace App\Usecases;

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

class GuestUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "GuestUsecase";
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $page       = $filterData['page'] ?? 1;
        $limit      = $filterData['limit'] ?? 10;
        $page       = ($page > 0 ? $page : 1);
        $filterName = $filterData['filter_name'] ?? "";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::GUEST, 'c')
                ->leftJoin("members as m", "m.id", "=", "c.member_id")
                ->whereNull("c.deleted_at")
                ->where('date', date_now());

            if (!empty($filterName)) {
                $data = $data->where('c.name', 'like', '%' . $filterName . '%');
            }

            $fields = ['c.*', 'm.name as member_name', 'm.identity_no as member_no'];

            $data = $data->orderBy("c.created_at", "desc")->paginate(20, $fields);

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
                ->table(DatabaseEntity::GUEST)
                ->whereNull("deleted_at")
                ->where('id', $id)
                ->first();

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

    public function create(int $memberID): array
    {
        $funcName = $this->className . ".create";

        DB::beginTransaction();
        try {
            $exist = DB::table(DatabaseEntity::GUEST)->where('member_id', $memberID)->where('date', date_now())->count();
            if ($exist) {
                return Response::buildSuccessCreated();
            }

            DB::table(DatabaseEntity::GUEST)
                ->insert([
                    'member_id'  => $memberID,
                    'date'       => date_now(),
                    'created_by' => Auth::user()->id,
                    'created_at' => datetime_now()
                ]);

            DB::commit();

            return Response::buildSuccessCreated();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        $return = [];
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {
            $delete = DB::table(DatabaseEntity::GUEST)
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

    public function getCountLast7Day(): array
    {
        $funcName = $this->className . ".getCountLast7Day";

        try {
            $guests = DB::select("SELECT 
                    DATE(date) as date, COUNT(id) as total_guests
                FROM guests
                WHERE date >= CURDATE() - INTERVAL 9 DAY -- Ambil 9 hari terakhir untuk mengantisipasi weekend
                AND WEEKDAY(date) < 5 -- Mengecualikan Sabtu (5) dan Minggu (6)
                GROUP BY DATE(date)
            ");

            $startDate = Carbon::now()->subDays(9); // Mengambil 9 hari terakhir untuk mengantisipasi weekend
            $endDate = Carbon::now();

            $days = collect();
            $workDaysCount = 0;

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
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

            $guestCollection = collect($guests);
            $guestCollection->each(function ($guest) use ($days) {
                $days->put($guest->date, $guest->total_guests);
            });

            $results = $days->map(function ($totalGuests, $date) {
                return [
                    'date' => $date,
                    'total' => $totalGuests,
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
                ->table(DatabaseEntity::GUEST)
                ->where('date', date_now())
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

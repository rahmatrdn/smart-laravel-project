<?php

namespace App\Usecases;

use App\Entities\BookEntity;
use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MemberUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "MemberUsecase";
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $page       = $filterData['page'] ?? 1;
        $limit      = $filterData['limit'] ?? 10;
        $page       = ($page > 0 ? $page : 1);
        $filterName = $filterData['filter_name'] ?? "";
        $filterCtg  = $filterData['filter_category_id'] ?? "";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::MEMBER, 'b')
                ->leftJoin("member_categories as bc", "bc.id", "=", "b.category_id")
                ->whereNull("b.deleted_at");

            if (!empty($filterName)) {
                $data = $data->where('b.name', 'like', '%' . $filterName . '%');
                $data = $data->orWhere('b.identity_no', $filterName);
            }
            if (!empty($filterCtg)) {
                $data = $data->where('b.category_id', (int) $filterCtg);
            }

            $fields = ['b.*', 'bc.name as category'];

            $data = $data->orderBy("b.created_at", "desc")->paginate(20, $fields)->appends(request()->query());

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
                ->table(DatabaseEntity::MEMBER, "b")
                ->leftJoin("member_categories as bc", "bc.id", "=", "b.category_id")
                ->whereNull("b.deleted_at")
                ->where('b.id', $id)
                ->first(['b.*', 'bc.name as category']);

            return Response::buildSuccess(
                data: collect($data)->toArray(),
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getByIdentityNo(string $id): array
    {
        $funcName = $this->className . ".getByIdentityNo";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::MEMBER, "b")
                ->leftJoin("member_categories as bc", "bc.id", "=", "b.category_id")
                ->whereNull("b.deleted_at")
                ->where('identity_no', $id)
                ->first(['b.*', 'bc.name as category']);

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
            'name' => 'required',
        ]);

        $customAttributes = [
            'name' => 'Nama',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();
        try {
            $memberID = DB::table(DatabaseEntity::MEMBER)
                ->insertGetId([
                    'name'          => $data['name'],
                    'category_id'   => $data['category_id'],
                    'identity_no'   => $data['identity_no'],
                    'join_year'     => $data['join_year'],
                    'created_by'    => Auth::user()->id,
                    'created_at'    => datetime_now()
                ]);

            if (empty($data['identity_no'])) {
                DB::table(DatabaseEntity::MEMBER)
                    ->where('id', $memberID)
                    ->update([
                        'identity_no' => $memberID
                    ]);
            }

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

    public function update(Request $data, int $id): array
    {
        $return = [];
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'name' => 'required',
        ]);

        $customAttributes = [
            'name' => 'Nama',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        $update = [
            'name'          => $data['name'],
            'category_id'   => $data['category_id'],
            'identity_no'   => $data['identity_no'],
            'join_year'     => $data['join_year'],
            'updated_by'     => Auth::user()->id,
            'updated_at'     => datetime_now()
        ];

        DB::beginTransaction();

        try {
            DB::table(DatabaseEntity::MEMBER)
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
            $delete = DB::table(DatabaseEntity::MEMBER)
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

    public function getByKeywordName(array $filterData = []): array
    {
        $funcName = $this->className . ".getByKeywordName";

        $term = $filterData['term'];

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::MEMBER, 'b')
                ->whereNull("b.deleted_at");

            $data = $data->where('b.name', 'like', '%' . $term . '%');
            $data = $data->orWhere('b.identity_no', '=', $term);
            $fields = ['b.*'];

            $data = $data->orderBy("b.created_at", "desc")->limit(30)->get($fields);

            return Response::buildSuccess(
                [
                    'list' => $data,
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

    public function getCountActive(): array
    {
        $funcName = $this->className . ".getCountActive";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::MEMBER)
                ->whereNull("deleted_at")
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

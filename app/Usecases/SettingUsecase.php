<?php

namespace App\Usecases;

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

class SettingUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "SettingUsecase";
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
                ->table(DatabaseEntity::MEMBER_CATEGORY, 'c')
                ->whereNull("c.deleted_at");

            if (!empty($filterName)) {
                $data = $data->where('c.name', 'like', '%' . $filterName . '%');
            }

            $fields = ['c.*'];

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

    public function get(): array
    {
        $funcName = $this->className . ".get";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::SETTING)
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
}

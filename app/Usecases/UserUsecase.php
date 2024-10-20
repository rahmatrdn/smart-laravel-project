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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "UserUsecase";
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::USER)
                ->whereNull("deleted_at")
                ->orderBy("created_at", "desc")
                ->paginate(20);

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

    public function getByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::USER)
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

    public function create(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            DB::table(DatabaseEntity::USER)
                ->insert([
                    'name'       => $data['name'],
                    'email'      => $data['email'],
                    'password'   => Hash::make('asdasd'),
                    'access_type' => 1,
                    'is_active'  => 1,
                    'created_by' => Auth::user()->id,
                    'created_at' => now(),
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

    public function update(Request $data, int $id): array
    {
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $validator->validate();

        $update = [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'updated_by' => Auth::user()->id,
            'updated_at' => now(),
        ];

        DB::beginTransaction();

        try {
            DB::table(DatabaseEntity::USER)
                ->where("id", $id)
                ->update($update);

            DB::commit();

            return Response::buildSuccess(
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
    }

    public function delete(int $id): array
    {
        $funcName = $this->className . ".delete";

        DB::beginTransaction();

        try {
            $delete = DB::table(DatabaseEntity::USER)
                ->where('id', $id)
                ->update([
                    'deleted_by' => Auth::user()->id,
                    'deleted_at' => now(),
                ]);

            if (!$delete) {
                DB::rollback();
                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_DELETED
            );
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    // public function changePassword(int $id, string $newPassword): array
    // {
    //     $funcName = $this->className . ".changePassword";

    //     $updateData = [
    //         'password' => Hash::make($newPassword),
    //         'updated_by' => Auth::user()->id,
    //         'updated_at' => now(),
    //     ];

    //     DB::beginTransaction();

    //     try {
    //         DB::table(DatabaseEntity::USER)
    //             ->where('id', $id)
    //             ->update($updateData);

    //         DB::commit();

    //         return Response::buildSuccess(
    //             message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
    //         );
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         Log::error($e->getMessage(), [
    //             'func_name' => $funcName,
    //             'user' => Auth::user()
    //         ]);

    //         return Response::buildErrorService($e->getMessage());
    //     }
    // }

    public function changePassword(array $data): array
    {
        $userID = Auth::user()->id;
        $return = [];
        $funcName = $this->className . ".changePassword";

        $validator = Validator::make($data, [
            'current_password' => 'required',
            'password'         => 'required',
            're_password'      => 'required|same:password',
        ]);

        $customAttributes = [
            'current_password' => 'Password Lama',
            'password'         => 'Password Baru',
            're_password'      => 'Ulangi Password Baru',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        $user = DB::connection(DatabaseEntity::SQL_READ)
            ->table(DatabaseEntity::USER)
            ->where('id', (int) $userID)
            ->first(['password']);

        if (!password_verify($data['current_password'], $user->password)) {
            return Response::buildErrorService("Password saat ini salah!");
        }

        DB::beginTransaction();

        try {
            $lockedPackage = DB::table(DatabaseEntity::USER)
                ->where('id', $userID)
                ->whereNull("deleted_at")
                ->lockForUpdate()
                ->first(['id']);

            if (!$lockedPackage) {
                DB::rollback();

                throw new Exception("FAILED LOCKED DATA");
            }

            DB::table(DatabaseEntity::USER)
                ->where("id", $userID)
                ->update([
                    'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: ResponseEntity::SUCCESS_MESSAGE_UPDATED
            );
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
            ]);

            return Response::buildErrorService($e->getMessage());
        }

        return $return;
    }

    public function resetPassword(int $id): array
    {
        $funcName = $this->className . ".resetPassword";

        $defaultPassword = 'asdasd'; // Password default

        DB::beginTransaction();

        try {
            // Update password menjadi default
            DB::table(DatabaseEntity::USER)
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($defaultPassword), // Gunakan Hash::make untuk hashing password
                    'updated_by' => Auth::user()->id,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return Response::buildSuccess(
                message: 'Password berhasil direset'
            );
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

}

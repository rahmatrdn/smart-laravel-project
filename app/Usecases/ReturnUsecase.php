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

const STATUS_DIKEMBALIKAN = 2;

class ReturnUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "ReturnUsecase";
    }

    public function return(Request $data): array
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
            'status'     => STATUS_DIKEMBALIKAN,
            'updated_by' => Auth::user()->id,
            'updated_at' => datetime_now()
        ];

        $loan = DB::table(DatabaseEntity::LOAN)->where("id", $loanID)->first();
        if ($loan->status == STATUS_DIKEMBALIKAN) {
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
}

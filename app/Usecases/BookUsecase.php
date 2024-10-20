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

class BookUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "BookUsecase";
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $page       = $filterData['page'] ?? 1;
        $limit      = $filterData['limit'] ?? 10;
        $page       = ($page > 0 ? $page : 1);

        $filterName         = $filterData['filter_name'] ?? "";
        $filterBookshelveID = $filterData['filter_bookshelve_id'] ?? "";
        $filterCategoryID   = $filterData['filter_category_id'] ?? "";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::BOOK, 'b')
                ->leftJoin("book_categories as bc", "bc.id", "=", "b.category_id")
                ->leftJoin("bookshelves as bs", "bs.id", "=", "b.bookshelve_id")
                ->whereNull("b.deleted_at");

            if (!empty($filterName)) {
                $data = $data->where('b.name', 'like', '%' . $filterName . '%');
            }
            if (!empty($filterBookshelveID)) {
                $data = $data->where('b.bookshelve_id', $filterBookshelveID);
            }
            if (!empty($filterCategoryID)) {
                $data = $data->where('b.category_id', $filterCategoryID);
            }

            $fields = ['b.*', 'bc.name as category', 'bs.name as bookshelve'];

            $data = $data->orderBy("b.created_at", "desc")->paginate(20, $fields);

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
                ->table(DatabaseEntity::BOOK)
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

    public function getDetailByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::BOOK, 'b')
                ->leftJoin("book_categories as bc", "bc.id", "=", "b.category_id")
                ->leftJoin("bookshelves as bs", "bs.id", "=", "b.bookshelve_id")
                ->whereNull("b.deleted_at")
                ->where('b.id', $id)
                ->first(['b.*', 'bc.name as category', 'bs.name as bookshelve']);

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

    public function getStocksByID(int $id): array
    {
        $funcName = $this->className . ".getStocksByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::BOOK_STOCK, 'b')
                ->where('b.book_id', $id)
                ->orderBy('created_at', "DESC")
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
            'name' => 'required',
        ]);

        $customAttributes = [
            'name' => 'Nama Kategori',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();
        try {
            $bookID =  DB::table(DatabaseEntity::BOOK)
                ->insertGetId([
                    'name'           => $data['name'],
                    'category_id'    => $data['category_id'],
                    'bookshelve_id'  => $data['bookshelve_id'],
                    'inventory_no'   => $data['inventory_no'],
                    'isbn'           => $data['isbn'],
                    'source'         => $data['source'],
                    'publisher'      => $data['publisher'],
                    'published_year' => $data['published_year'],
                    'total_stock'    => $data['total_stock'],
                    'author'         => $data['author'],
                    'created_by'     => Auth::user()->id,
                    'created_at'     => datetime_now()
                ]);

            DB::table(DatabaseEntity::BOOK_STOCK)
                ->insert([
                    'book_id'     => $bookID,
                    'description' => "Inisiasi stok awal",
                    'type'        => 1,
                    'quantity'    => $data['total_stock'],
                    'created_by'  => Auth::user()->id,
                    'created_at'  => datetime_now()
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
            'name'           => $data['name'],
            'category_id'    => $data['category_id'],
            'bookshelve_id'  => $data['bookshelve_id'],
            'inventory_no'   => $data['inventory_no'],
            'isbn'           => $data['isbn'],
            'source'         => $data['source'],
            'publisher'      => $data['publisher'],
            'published_year' => $data['published_year'],
            'total_stock'    => $data['total_stock'],
            'author'         => $data['author'],
            'updated_by'     => Auth::user()->id,
            'updated_at'     => datetime_now()
        ];

        $newStock = $data['total_stock'];
        if ($data['current_stock'] != $newStock) {
            if ($data['current_stock'] > $newStock) {
                $newStock = $data['current_stock'] - $newStock;
                $type = BookEntity::STOCK_OUT;
            } else {
                $newStock = $newStock - $data['current_stock'];
                $type = BookEntity::STOCK_IN;
            }

            DB::table(DatabaseEntity::BOOK_STOCK)
                ->insert([
                    'book_id'     => $id,
                    'description' => "Perubahan Stok dari form update data",
                    'type'        => $type,
                    'quantity'    => $newStock,
                    'created_by'  => Auth::user()->id,
                    'created_at'  => datetime_now()
                ]);
        }

        DB::beginTransaction();

        try {
            DB::table(DatabaseEntity::BOOK)
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
            $delete = DB::table(DatabaseEntity::BOOK)
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
                ->table(DatabaseEntity::BOOK, 'b')
                ->whereNull("b.deleted_at");

            $data = $data->where('b.name', 'like', '%' . $term . '%');
            $data = $data->orWhere('b.id', '=', $term);
            $fields = ['b.*'];

            $data = $data->orderBy("b.created_at", "desc")->get($fields);

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

    public function getExcelFile(Request $request): array
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);
        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $bookCtgs = DB::table(DatabaseEntity::BOOK_CATEGORY)->whereNull("deleted_at")->get();
        $bookshelves = DB::table(DatabaseEntity::BOOKSHELVE)->whereNull("deleted_at")->get();

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $data = [];
        $count = 0;
        foreach ($sheet->getRowIterator() as $row) {
            $count++;
            if ($count == 1) {
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $rowData[9] = $bookCtgs->where("name", strtoupper($rowData[2] ?? "-"))->first()->id ?? 1;
            $rowData[10] = $bookshelves->where("name", strtoupper($rowData[1] ?? "-"))->first()->id ?? 1;
            $rowData[11] = getBookSourceName(strtoupper($rowData[8] ?? "-"));

            $data[] = $rowData;
        }

        return $data;
    }

    public function importProcess(Request $data): array
    {
        $funcName = $this->className . ".create";

        $books = $data->input("books");
        $books = json_decode($books);

        DB::beginTransaction();
        try {
            foreach ($books as $b) {
                $bookID =  DB::table(DatabaseEntity::BOOK)
                    ->insertGetId([
                        'name'           => $b[0],
                        'category_id'    => $b[9],
                        'bookshelve_id'  => $b[10],
                        'inventory_no'   => $b[3],
                        'isbn'           => $b[6],
                        'source'         => $b[11],
                        'publisher'      => $b[4],
                        'published_year' => $b[5],
                        'total_stock'    => $b[7],
                        'created_by'     => Auth::user()->id,
                        'created_at'     => datetime_now()
                    ]);

                DB::table(DatabaseEntity::BOOK_STOCK)
                    ->insert([
                        'book_id'     => $bookID,
                        'description' => "Inisiasi stok awal",
                        'type'        => 1,
                        'quantity'    => $b[7] ?? 1,
                        'created_by'  => Auth::user()->id,
                        'created_at'  => datetime_now()
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

    public function getCountActive(): array
    {
        $funcName = $this->className . ".getCountActive";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::BOOK)
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

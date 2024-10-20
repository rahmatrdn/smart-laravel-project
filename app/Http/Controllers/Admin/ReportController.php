<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Usecases\BookUsecase;
use App\Usecases\GuestUsecase;
use App\Usecases\LoanUsecase;
use App\Usecases\MemberUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $loanUsc;
    protected $guestUsc;
    protected $bookUsc;
    protected $memberUsc;

    public function __construct(
        LoanUsecase $loanUsc,
        GuestUsecase $guestUsc,
        BookUsecase $bookUsc,
        MemberUsecase $memberUsc,
    ) {
        $this->loanUsc = $loanUsc;
        $this->guestUsc = $guestUsc;
        $this->bookUsc = $bookUsc;
        $this->memberUsc = $memberUsc;
    }

    public function getActivityPerDays(): JsonResponse
    {
        $last7daysLoan = $this->loanUsc->getCountLast7Day();
        $last7daysGuest = $this->guestUsc->getCountLast7Day();

        $data = [
            "dates" => collect($last7daysLoan['data'])->pluck('date'),
            "series" => [
                [
                    "name" => "Peminjaman",
                    "data" => collect($last7daysLoan['data'])->pluck('total')->values()->all()
                ],
                [
                    "name" => "Pengunjung",
                    "data" => collect($last7daysGuest['data'])->pluck('total')->values()->all()
                ],
            ]
        ];

        return response()->json($data);
    }

    public function getTotalVisitorToday(): JsonResponse
    {
        return response()->json([
            "total" => $this->guestUsc->getCountToday()['data']['count']
        ]);
    }
    public function getTotalLoanToday(): JsonResponse
    {
        return response()->json([
            "total" => $this->loanUsc->getCountToday()['data']['count']
        ]);
    }
    public function getTotalBooksToday(): JsonResponse
    {
        return response()->json([
            "total" => $this->bookUsc->getCountActive()['data']['count']
        ]);
    }
    public function getTotalMembersToday(): JsonResponse
    {
        return response()->json([
            "total" => $this->memberUsc->getCountActive()['data']['count']
        ]);
    }

    public function getTop10LateReturn(): JsonResponse
    {
        return response()->json([
            'status' => "success",
            'data' => $this->loanUsc->getMemberLate()['data']
        ]);
    }
}

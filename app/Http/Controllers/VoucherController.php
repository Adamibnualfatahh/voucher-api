<?php

namespace App\Http\Controllers;

use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(protected readonly VoucherService $voucherService)
    {
    }

    public function generate(): JsonResponse
    {
        $voucher = $this->voucherService->generateVoucher();
        return response()->json($voucher);
    }

    public function index(Request $request): JsonResponse
    {
        $vouchers = $this->voucherService->getAllVouchers($request->all());
        return response()->json($vouchers);
    }

    public function show($code): JsonResponse
    {
        $voucher = $this->voucherService->getVoucherByCode($code);
        return response()->json($voucher);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoucherIndexRequest;
use App\Http\Resources\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VoucherController extends Controller
{
    public function __construct(protected readonly VoucherService $voucherService)
    {
    }

    public function generate(): VoucherResource
    {
        $voucher = $this->voucherService->generateVoucher();
        return new VoucherResource($voucher);
    }

    public function index(VoucherIndexRequest $request): AnonymousResourceCollection
    {
        $vouchers = $this->voucherService->getAllVouchers($request->validated());
        return VoucherResource::collection($vouchers);
    }

    public function show($code): VoucherResource|JsonResponse
    {
        return new VoucherResource($this->voucherService->getVoucherByCode($code));
    }
}

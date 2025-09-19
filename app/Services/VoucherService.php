<?php

namespace App\Services;

use App\Interfaces\VoucherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use App\Models\Voucher;

readonly class VoucherService
{
    public function __construct(protected VoucherRepositoryInterface $voucherRepository)
    {
    }

    public function generateVoucher(): Voucher
    {
        $code = Str::random(10);
        return $this->voucherRepository->create(['code' => $code]);
    }

    public function getAllVouchers(array $params): LengthAwarePaginator
    {
        return $this->voucherRepository->getAll($params);
    }

    public function getVoucherByCode(string $code): ?Voucher
    {
        return $this->voucherRepository->findByCode($code);
    }
}

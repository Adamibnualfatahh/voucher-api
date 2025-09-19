<?php

namespace App\Interfaces;

use App\Models\Voucher;

interface VoucherRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $code): ?Voucher;
}

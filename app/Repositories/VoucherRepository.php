<?php

namespace App\Repositories;

use App\Interfaces\VoucherRepositoryInterface;
use App\Models\Voucher;

class VoucherRepository extends EloquentRepository implements VoucherRepositoryInterface
{
    public function __construct(Voucher $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Voucher
    {
        return $this->model->where('code', $code)->firstOrFail();
    }
}

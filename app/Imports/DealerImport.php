<?php

namespace App\Imports;

use App\Models\Dealer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DealerImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Dealer([
            'code' => $row['kode'],
            'name' => $row['nama'],
            'address' => $row['alamat'],
            'phone' => $row['telepon'],
            'status' => $row['status'] == 'Aktif' ? true : false,
        ]);
    }
}

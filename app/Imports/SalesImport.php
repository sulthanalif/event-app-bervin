<?php

namespace App\Imports;

use App\Models\Dealer;
use App\Models\Sales;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        $dealer = Dealer::where('code', $row['kode_dealer'])->first();

        return new Sales([
            'code' => $row['id_salesman'],
            'name' => $row['nama_salesman'],
            'status' => $row['status'] == 'Aktif' ? true : false,
            'dealer_id' => $dealer->id
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Imports\DealerImport;
use App\Imports\ProductImport;
use App\Imports\SalesImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new DealerImport, public_path('templates/template-dealer.xlsx'));
        Excel::import(new ProductImport, public_path('templates/template-product.xlsx'));
        Excel::import(new SalesImport, public_path('templates/template-sales.xlsx'));
    }
}

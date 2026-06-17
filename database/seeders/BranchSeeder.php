<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Seed the branches used for development and testing.
     */
    public function run(): void
    {
        $branches = [
            ['name' => 'Filial São Paulo', 'code' => 'FIL-SP01', 'city' => 'São Paulo', 'state' => 'SP'],
            ['name' => 'Filial Rio de Janeiro', 'code' => 'FIL-RJ01', 'city' => 'Rio de Janeiro', 'state' => 'RJ'],
            ['name' => 'Filial Belo Horizonte', 'code' => 'FIL-MG01', 'city' => 'Belo Horizonte', 'state' => 'MG'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], $branch);
        }
    }
}

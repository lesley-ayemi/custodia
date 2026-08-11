<?php

namespace Database\Seeders;

use App\Enums\PrisonerStatus;
use App\Enums\Role;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Prisoner;
use App\Models\User;
use App\Services\HousingService;
use Illuminate\Database\Seeder;

class HousingSeeder extends Seeder
{
    /**
     * Blocks, each with a number of cells and a fixed capacity per cell.
     *
     * @var array<string, array{cells: int, capacity: int}>
     */
    protected array $layout = [
        'Block A' => ['cells' => 10, 'capacity' => 2],
        'Block B' => ['cells' => 8, 'capacity' => 2],
        'Block C' => ['cells' => 6, 'capacity' => 2],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cells = [];

        foreach ($this->layout as $blockName => $config) {
            $block = Block::create(['name' => $blockName]);
            $prefix = strtoupper(substr($blockName, -1));

            for ($i = 1; $i <= $config['cells']; $i++) {
                $cells[] = $block->cells()->create([
                    'code' => sprintf('%s-%03d', $prefix, 100 + $i),
                    'capacity' => $config['capacity'],
                ]);
            }
        }

        $officer = User::where('role', Role::Officer)->firstOrFail();
        $housing = app(HousingService::class);

        $remainingCapacity = [];
        foreach ($cells as $cell) {
            $remainingCapacity[$cell->id] = $cell->capacity;
        }

        Prisoner::where('status', PrisonerStatus::InCustody)
            ->orderBy('id')
            ->each(function (Prisoner $prisoner) use (&$remainingCapacity, $cells, $housing, $officer) {
                foreach ($cells as $cell) {
                    if ($remainingCapacity[$cell->id] > 0) {
                        $housing->assign($prisoner, $cell, $officer);
                        $remainingCapacity[$cell->id]--;

                        return;
                    }
                }
            });
    }
}

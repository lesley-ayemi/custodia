<?php

namespace App\Http\Controllers\Api;

use App\Enums\IncidentStatus;
use App\Enums\PrisonerStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\IncidentResource;
use App\Models\Block;
use App\Models\HousingAssignment;
use App\Models\Incident;
use App\Models\Prisoner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $blocks = $this->blockOccupancy();

        $totalCapacity = (int) $blocks->sum('capacity');
        $totalOccupied = (int) $blocks->sum('occupancy');

        $recentIncidents = Incident::with(['prisoner', 'officer'])
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get();

        return response()->json([
            'total_prisoners' => Prisoner::whereNull('archived_at')->count(),
            'in_custody_prisoners' => Prisoner::where('status', PrisonerStatus::InCustody)->whereNull('archived_at')->count(),
            'occupancy_percent' => $this->percent($totalOccupied, $totalCapacity),
            'open_incidents' => Incident::whereIn('status', [IncidentStatus::Reported, IncidentStatus::UnderReview])->count(),
            'available_beds' => max(0, $totalCapacity - $totalOccupied),
            'recent_incidents' => IncidentResource::collection($recentIncidents),
            'block_occupancy' => $blocks->map(fn (Block $block) => [
                'name' => $block->name,
                'percent' => $this->percent((int) $block->occupancy, (int) $block->capacity),
            ])->values(),
        ]);
    }

    /**
     * One row per block carrying its total capacity and its live occupancy, both
     * summed in the database.
     *
     * The readable version of this walked blocks -> wings -> cells and called
     * occupancy() on each cell, which cost a count query per cell: 24 of the
     * dashboard's 34 queries on the demo estate, and one more for every cell
     * ever added. The database is also in another region now, so each of those
     * was a round trip.
     *
     * @return Collection<int, Block>
     */
    private function blockOccupancy(): Collection
    {
        return Block::query()
            ->withSum('cells as capacity', 'capacity')
            ->addSelect(['occupancy' => HousingAssignment::query()
                ->selectRaw('count(*)')
                ->join('cells', 'cells.id', '=', 'housing_assignments.cell_id')
                ->join('wings', 'wings.id', '=', 'cells.wing_id')
                ->whereColumn('wings.block_id', 'blocks.id')
                ->whereNull('housing_assignments.ended_at'),
            ])
            ->orderBy('name')
            ->get();
    }

    private function percent(int $occupied, int $capacity): int
    {
        return $capacity > 0 ? (int) round(($occupied / $capacity) * 100) : 0;
    }
}

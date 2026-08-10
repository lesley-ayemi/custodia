<?php

namespace App\Http\Controllers\Api;

use App\Enums\IncidentStatus;
use App\Enums\PrisonerStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\IncidentResource;
use App\Models\Block;
use App\Models\Cell;
use App\Models\Incident;
use App\Models\Prisoner;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalPrisoners = Prisoner::whereNull('archived_at')->count();

        $totalCapacity = Cell::sum('capacity');
        $totalOccupied = Cell::withCount(['activeAssignments'])->get()->sum('active_assignments_count');
        $occupancyPercent = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100) : 0;

        $openIncidents = Incident::whereIn('status', [IncidentStatus::Reported, IncidentStatus::UnderReview])->count();

        $recentIncidents = Incident::with(['prisoner', 'officer'])
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get();

        $blockOccupancy = Block::with('cells')->get()->map(function (Block $block) {
            $capacity = $block->cells->sum('capacity');
            $occupied = $block->cells->sum(fn (Cell $cell) => $cell->occupancy());

            return [
                'name' => $block->name,
                'percent' => $capacity > 0 ? round(($occupied / $capacity) * 100) : 0,
            ];
        });

        return response()->json([
            'total_prisoners' => $totalPrisoners,
            'in_custody_prisoners' => Prisoner::where('status', PrisonerStatus::InCustody)->whereNull('archived_at')->count(),
            'occupancy_percent' => $occupancyPercent,
            'open_incidents' => $openIncidents,
            'available_beds' => max(0, $totalCapacity - $totalOccupied),
            'recent_incidents' => IncidentResource::collection($recentIncidents),
            'block_occupancy' => $blockOccupancy,
        ]);
    }
}

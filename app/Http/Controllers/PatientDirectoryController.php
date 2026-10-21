<?php

namespace App\Http\Controllers;

use App\Models\PatientProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'in:F,M,O'],
        ]);

        $patients = PatientProfile::query()
            ->with('user')
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $like = '%'.$term.'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query->where('national_id', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhereHas('user', function (Builder $userQuery) use ($like): void {
                            $userQuery->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                });
            })
            ->when($filters['sex'] ?? null, fn (Builder $query, string $sex) => $query->where('sex', $sex))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients', 'filters'));
    }
}

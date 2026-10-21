<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpecialtyController extends Controller
{
    public function index(): View
    {
        $specialties = Specialty::query()
            ->withCount('doctors')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create(): View
    {
        return view('admin.specialties.create', ['specialty' => new Specialty]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');
        Specialty::query()->create($data);

        return redirect()->route('admin.specialties.index')->with('status', 'La especialidad fue creada.');
    }

    public function edit(Specialty $specialty): View
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty): RedirectResponse
    {
        $data = $request->validate($this->rules($specialty));
        $data['is_active'] = $request->boolean('is_active');
        $specialty->update($data);

        return redirect()->route('admin.specialties.index')->with('status', 'La especialidad fue actualizada.');
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        if ($specialty->doctors()->exists()) {
            return back()->with('error', 'No se puede eliminar una especialidad asignada a médicos.');
        }

        $specialty->delete();

        return redirect()->route('admin.specialties.index')->with('status', 'La especialidad fue eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Specialty $specialty = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('specialties', 'name')->ignore($specialty)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

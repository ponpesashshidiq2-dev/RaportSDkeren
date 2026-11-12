<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExtracurricularController extends Controller
{
    public function index(): View
    {
        $extracurriculars = Extracurricular::query()->orderBy('name')->get();

        return view('extracurriculars.index', compact('extracurriculars'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:extracurriculars,name'],
            'description' => ['nullable', 'string'],
        ]);

        Extracurricular::create($data);

        return redirect()->route('extracurriculars.index')->with('success', 'Jenis ekstrakurikuler berhasil ditambahkan.');
    }

    public function update(Request $request, Extracurricular $extracurricular): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:extracurriculars,name,'.$extracurricular->id],
            'description' => ['nullable', 'string'],
        ]);

        $extracurricular->update($data);

        return redirect()->route('extracurriculars.index')->with('success', 'Jenis ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Extracurricular $extracurricular): RedirectResponse
    {
        $extracurricular->delete();

        return redirect()->route('extracurriculars.index')->with('success', 'Jenis ekstrakurikuler berhasil dihapus.');
    }
}

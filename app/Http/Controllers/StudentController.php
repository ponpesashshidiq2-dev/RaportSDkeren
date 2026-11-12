<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $classrooms = Classroom::query()->orderBy('grade')->orderBy('name')->get();

        $students = Student::query()
            ->with('classroom')
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('classroom_id', $request->integer('kelas_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($qq) use ($term) {
                    $qq->where('nama', 'like', "%{$term}%")
                        ->orWhere('nisn', 'like', "%{$term}%")
                        ->orWhere('nis', 'like', "%{$term}%");
                });
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('students.index', compact('students', 'classrooms'));
    }

    public function create(): View
    {
        return view('students.form', [
            'student' => new Student(),
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Student::create($this->validated($request));

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('students.form', [
            'student' => $student,
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->update($this->validated($request, $student));

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function importForm(): View
    {
        return view('students.import', [
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
        ]);
    }

    /**
     * Import Excel/CSV data siswa (Laravel Excel / SheetJS-compatible .xlsx).
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'kelas_id' => ['nullable', 'exists:classrooms,id'],
        ], [
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
        ]);

        StudentsImport::$importedCount = 0;

        DB::transaction(function () use ($request) {
            Excel::import(
                new StudentsImport($request->filled('kelas_id') ? $request->integer('kelas_id') : null),
                $request->file('file')
            );
        });

        $count = StudentsImport::$importedCount;

        return redirect()->route('students.index')
            ->with('success', "Import selesai. {$count} baris data siswa berhasil diproses.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'.($student ? ','.$student->id : '')],
            'nis' => ['nullable', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}

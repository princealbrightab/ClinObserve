<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfessorRequest;
use App\Models\User;
use App\Services\StudentService;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfessorController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $q = trim((string) $request->query('q'));
        $professors = User::where('role', UserRole::Professor)->withCount('assignedStudents')
            ->when($q, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('professors.index', compact('professors', 'q'));
    }

    public function create(): View
    {
        return view('professors.form', ['professor' => new User]);
    }

    public function store(ProfessorRequest $request): RedirectResponse
    {
        $professor = new User($request->safe()->only(['name', 'email', 'password']));
        $professor->role = UserRole::Professor;
        $professor->created_by = $request->user()->id;
        $professor->is_active = true;
        $professor->must_change_password = true;
        $professor->save();

        return redirect()->route('hod.professors.show', $professor)->with('success', 'Professor created. Share the temporary password securely; a change is required at first login.');
    }

    public function show(User $professor): View
    {
        abort_unless($professor->isProfessor(), 404);
        $students = $professor->assignedStudents()->with('studentProfile')->withCount('encounters')->orderBy('name')->paginate(15);

        return view('professors.show', compact('professor', 'students'));
    }

    public function edit(User $professor): View
    {
        abort_unless($professor->isProfessor(), 404);

        return view('professors.form', compact('professor'));
    }

    public function update(ProfessorRequest $request, User $professor): RedirectResponse
    {
        $professor->update($request->safe()->only(['name', 'email']));

        return redirect()->route('hod.professors.show', $professor)->with('success', 'Professor details updated.');
    }

    public function status(Request $request, User $professor): RedirectResponse
    {
        abort_unless($professor->isProfessor(), 404);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        DB::transaction(function () use ($professor, $data): void {
            $professor->is_active = (bool) $data['is_active'];
            $professor->remember_token = Str::random(60);
            $professor->save();
            DB::table('sessions')->where('user_id', $professor->id)->delete();
        });

        return back()->with('success', 'Professor account status updated.');
    }

    public function credentials(Request $request, User $professor, StudentService $service): RedirectResponse
    {
        abort_unless($professor->isProfessor(), 404);
        $data = $request->validate(['password' => ['required', 'confirmed', Password::min(12)->letters()->numbers(), 'max:255']]);
        $service->resetCredentials($professor, $data['password']);

        return back()->with('success', 'Temporary password reset. Existing sessions were revoked.');
    }
}

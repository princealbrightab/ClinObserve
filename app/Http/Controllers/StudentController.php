<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $q = trim((string) $request->query('q'));
        $students = User::where('role', 'student')->with('studentProfile')->withCount('encounters')
            ->when($q, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%')->orWhereHas('studentProfile', fn ($query) => $query->where('roll_number', 'like', '%'.$q.'%'))))->orderBy('name')->paginate(15)->withQueryString();

        return view('students.index', compact('students', 'q'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('students.form', ['student' => new User]);
    }

    public function store(StudentRequest $request, StudentService $service): RedirectResponse
    {
        $student = $service->save($request->validated(), $request->user());

        return redirect()->route('hod.students.show', $student)->with('success', 'Student created. Share the initial password securely; a change is required at first login.');
    }

    public function show(User $student): View
    {
        abort_unless($student->isStudent(), 404);
        Gate::authorize('view', $student);
        $student->load('studentProfile');
        $encounters = $student->encounters()->with('patient')->latest('attended_at')->paginate(10);

        return view('students.show', compact('student', 'encounters'));
    }

    public function edit(User $student): View
    {
        Gate::authorize('update', $student);
        $student->load('studentProfile');

        return view('students.form', compact('student'));
    }

    public function update(StudentRequest $request, User $student, StudentService $service): RedirectResponse
    {
        $service->save($request->validated(), $request->user(), $student);

        return redirect()->route('hod.students.show', $student)->with('success', 'Student details updated.');
    }

    public function status(Request $request, User $student): RedirectResponse
    {
        Gate::authorize('update', $student);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        DB::transaction(function () use ($student, $data): void {
            $student->is_active = (bool) $data['is_active'];
            $student->remember_token = Str::random(60);
            $student->save();
            DB::table('sessions')->where('user_id', $student->id)->delete();
        });

        return back()->with('success', 'Account status updated.');
    }

    public function credentials(Request $request, User $student, StudentService $service): RedirectResponse
    {
        Gate::authorize('update', $student);
        $data = $request->validate(['password' => ['required', 'confirmed', Password::min(12)->letters()->numbers(), 'max:255']]);
        $service->resetCredentials($student, $data['password']);

        return back()->with('success', 'Temporary password reset. Existing sessions were revoked.');
    }
}

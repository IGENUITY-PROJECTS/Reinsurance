<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FeedbackTopicRoute;
use App\Services\FeedbackRouting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    private function authorize(Request $request): void
    {
        abort_unless($request->user()->hasRole('super-admin'), 403);
    }

    public function index(Request $request)
    {
        $this->authorize($request);
        $request->validate(['q' => 'nullable|string|max:120']);

        return view('admin.portal-setup', [
            'departments' => Department::withCount('users')->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q')->toString().'%'))->orderBy('name')->paginate(10)->withQueryString(),
            'activeDepartments' => Department::where('is_active', true)->orderBy('name')->get(),
            'topics' => FeedbackRouting::TOPICS, 'topicRoutes' => FeedbackTopicRoute::pluck('department_id', 'topic'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize($request);
        $input = $request->validate(['name' => ['required', 'string', 'max:120', 'unique:departments,name']]);
        Department::create(['name' => $input['name'], 'code' => Str::slug($input['name']).'-'.Str::lower(Str::random(6))]);

        return back()->with('status', 'Department created. Assign broker staff and feedback topics below.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize($request);
        $department = Department::findOrFail($id);
        $input = $request->validate(['name' => ['required', 'string', 'max:120', Rule::unique('departments')->ignore($id)], 'is_active' => ['required', 'boolean']]);
        if ($department->is_default && ! $input['is_active']) {
            throw ValidationException::withMessages(['department' => 'The default department must remain active.']);
        }
        $department->update($input);

        return back()->with('status', 'Department updated.');
    }

    public function routing(Request $request)
    {
        $this->authorize($request);
        $rules = ['routes' => ['required', 'array:general,claims,premiums,adjustments']];
        foreach (array_keys(FeedbackRouting::TOPICS) as $topic) {
            $rules['routes.'.$topic] = ['required', 'integer', Rule::exists('departments', 'id')->where('is_active', true)];
        }
        $input = $request->validate($rules);
        DB::transaction(function () use ($input) {
            foreach ($input['routes'] as $topic => $id) {
                FeedbackTopicRoute::updateOrCreate(['topic' => $topic], ['department_id' => $id]);
            }
        });

        return back()->with('status', 'Feedback routing updated for new conversations.');
    }
}

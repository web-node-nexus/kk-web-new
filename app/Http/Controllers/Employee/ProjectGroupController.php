<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ClientProject;
use App\Models\ClientProjectMember;
use App\Models\Employee;
use App\Models\EmployeeNotification;
use App\Models\ProjectGroupMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectGroupController extends Controller
{
    public function index(): View
    {
        $employee = $this->employeeOrAbort();

        $projects = ClientProject::query()
            ->whereHas('members', fn ($q) => $q->where('employee_id', $employee->id))
            ->withCount('members')
            ->with(['members.employee'])
            ->orderByDesc('id')
            ->get();

        return view('employee.projects.index', [
            'projects' => $projects,
            'employee' => $employee,
        ]);
    }

    public function show(int $id): View
    {
        $employee = $this->employeeOrAbort();
        $project = $this->projectForMember($employee->id, $id);

        $members = $project->members()
            ->with('employee')
            ->get()
            ->map(fn (ClientProjectMember $m) => [
                'id' => $m->employee_id,
                'name' => $m->employee?->name ?? 'Member',
                'code' => $m->employee?->employee_code,
                'role' => $m->role_label ?: ($m->employee?->role_title ?: 'Member'),
                'photo' => $m->employee?->photoUrl(),
            ])
            ->values();

        return view('employee.projects.show', [
            'project' => $project,
            'employee' => $employee,
            'members' => $members,
        ]);
    }

    public function messages(Request $request, int $id): JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $project = $this->projectForMember($employee->id, $id);
        $after = (int) $request->query('after', 0);

        $rows = ProjectGroupMessage::query()
            ->with('employee')
            ->where('client_project_id', $project->id)
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (ProjectGroupMessage $m) => $m->toChatArray());

        return response()->json(['messages' => $rows]);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $project = $this->projectForMember($employee->id, $id);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $text = trim($data['message']);
        if ($text === '') {
            return response()->json(['message' => 'Message required.'], 422);
        }

        $memberIds = ClientProjectMember::query()
            ->where('client_project_id', $project->id)
            ->pluck('employee_id')
            ->all();

        $members = Employee::query()
            ->whereIn('id', $memberIds)
            ->get(['id', 'name', 'employee_code']);

        $mentionedIds = $this->parseMentions($text, $members);

        $msg = ProjectGroupMessage::query()->create([
            'client_project_id' => $project->id,
            'employee_id' => $employee->id,
            'message' => $text,
            'mentions' => $mentionedIds ?: null,
        ]);
        $msg->load('employee');

        foreach ($mentionedIds as $mentionId) {
            if ((int) $mentionId === (int) $employee->id) {
                continue;
            }
            EmployeeNotification::query()->create([
                'employee_id' => $mentionId,
                'type' => 'project_mention',
                'title' => $employee->name.' mentioned you',
                'body' => 'In project group: '.$project->name,
                'url' => route('employee.projects.show', $project->id),
            ]);
        }

        return response()->json(['message' => $msg->toChatArray()]);
    }

    /** @param \Illuminate\Support\Collection<int, Employee> $members */
    protected function parseMentions(string $text, $members): array
    {
        $ids = [];
        foreach ($members as $member) {
            $name = trim((string) $member->name);
            if ($name === '') {
                continue;
            }
            // Match @Full Name (allow spaces) before punctuation/end
            $pattern = '/@'.preg_quote($name, '/').'(?=\s|[.,!?;:]|$)/iu';
            if (preg_match($pattern, $text)) {
                $ids[] = (int) $member->id;
                continue;
            }
            $code = trim((string) ($member->employee_code ?? ''));
            if ($code !== '' && preg_match('/@'.preg_quote($code, '/').'(?=\s|[.,!?;:]|$)/iu', $text)) {
                $ids[] = (int) $member->id;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function projectForMember(int $employeeId, int $projectId): ClientProject
    {
        return ClientProject::query()
            ->whereKey($projectId)
            ->whereHas('members', fn ($q) => $q->where('employee_id', $employeeId))
            ->firstOrFail();
    }

    protected function employeeOrAbort(): Employee
    {
        $employee = Auth::user()?->employee;
        abort_unless($employee, 403, 'No employee profile linked.');

        return $employee;
    }
}

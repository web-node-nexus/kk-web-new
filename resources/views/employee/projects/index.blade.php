@extends('layouts.employee')
@section('title', 'Project Groups')
@section('heading', 'Project Groups')
@section('subheading', 'Groups for projects you are assigned to. Chat and @mention teammates.')

@section('content')
<div class="ep-card">
    <div class="ep-card__h">
        <h3>Your project groups</h3>
        <span class="ep-muted" style="font-size:12px">Admin adds you to a project team — then it appears here.</span>
    </div>
    <div class="ep-card__b" style="display:grid;gap:12px">
        @forelse ($projects as $project)
            <a href="{{ route('employee.projects.show', $project->id) }}" class="ep-group-card">
                <div class="ep-group-card__main">
                    <strong>{{ $project->name }}</strong>
                    <span>{{ $project->client_name }} · {{ $project->code() }}</span>
                    <em>{{ $project->members_count }} {{ $project->members_count === 1 ? 'member' : 'members' }}</em>
                </div>
                <div class="ep-group-card__avatars">
                    @foreach ($project->members->take(4) as $m)
                        @if ($m->employee?->photoUrl())
                            <img src="{{ $m->employee->photoUrl() }}" alt="">
                        @else
                            <span>{{ strtoupper(substr($m->employee?->name ?? 'M', 0, 1)) }}</span>
                        @endif
                    @endforeach
                </div>
            </a>
        @empty
            <div class="ep-empty">
                <strong>No project groups yet</strong>
                When admin adds you to a project team, the group chat will show up here.
            </div>
        @endforelse
    </div>
</div>
@endsection

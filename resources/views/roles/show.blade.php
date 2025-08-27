@extends('layouts.app')

@section('title', __('Role View') . ' - ' . $role->name)

@section('content')
    {!! breadcrumb([
        ['title' => __('Settings')],
        ['title' => __('Roles')],
        ['title' => __('Role View') . ' - ' . $role->name],
    ]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('Role View') }}</h5>
                    <div class="card-body">
                        {{-- Role Name --}}
                        <div class="mb-3">
                            <label class="fw-bold">{{ __('Role Name') }}</label>
                            <p class="form-control-plaintext">{{ $role->name }}</p>
                        </div>

                        {{-- Modules & Permissions --}}
                        <div class="mb-3">
                            <table class="table text-center">
                                <thead>
                                    <tr>
                                        <th>{{ __('Module') }}</th>
                                        <th>{{ __('Show in Sidebar') }}</th>
                                        <th>{{ __('View') }}</th>
                                        <th>{{ __('Add') }}</th>
                                        <th>{{ __('Edit') }}</th>
                                        <th>{{ __('Delete') }}</th>
                                        <th>{{ __('All') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules->where('parent_id', null) as $parent)
                                        <tr class="table-primary">
                                            <td><strong>{{ __($parent->label) }}</strong></td>
                                            <td>
                                                @if (in_array($parent->id, $sidebarModules))
                                                    ✅
                                                @else
                                                    ❌
                                                @endif
                                            </td>
                                            <td colspan="5"></td>
                                        </tr>
                                        @foreach ($modules->where('parent_id', $parent->id) as $child)
                                            <tr>
                                                <td style="padding-right:30px;">&#8627; {{ __($child->label) }}</td>
                                                <td>
                                                    @if (in_array($child->id, $sidebarModules))
                                                        ✅
                                                    @else
                                                        ❌
                                                    @endif
                                                </td>
                                                @foreach (['view', 'create', 'edit', 'delete'] as $action)
                                                    <td>
                                                        @if (in_array(strtolower($action . ' ' . str_replace(['_', '-'], ' ', $child->name)), $rolePermissions))
                                                            ✅
                                                        @else
                                                            ❌
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td>
                                                    {{-- all --}}
                                                    @php
                                                        $allChecked = true;
                                                        foreach (['view', 'create', 'edit', 'delete'] as $action) {
                                                            if (
                                                                !in_array(
                                                                    strtolower(
                                                                        $action .
                                                                            ' ' .
                                                                            str_replace(['_', '-'], ' ', $child->name),
                                                                    ),
                                                                    $rolePermissions,
                                                                )
                                                            ) {
                                                                $allChecked = false;
                                                            }
                                                        }
                                                        if (!in_array($child->id, $sidebarModules)) {
                                                            $allChecked = false;
                                                        }
                                                    @endphp
                                                    {{ $allChecked ? '✅' : '❌' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Back button --}}
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="icon-base ti tabler-arrow-left me-1"></i>
                            {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

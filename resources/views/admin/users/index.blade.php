@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="surface-card-strong p-6 md:p-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <p class="section-kicker">Admin Directory</p>
            <h2 class="section-title mt-1">Manage Users</h2>
        </div>

        <div class="flex items-center gap-2">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
                       class="input-field !py-2 text-sm min-w-60">
                <button class="btn-pill btn-pill-soft !px-4 !py-2 text-sm">Filter</button>
            </form>
        </div>
    </div>

    @if(! $admin->isSuperAdmin())
        <div class="mb-5 border-2 border-blue-300 bg-blue-50 px-5 py-4 font-manrope text-sm text-blue-700">
            <p class="font-space font-bold">Manage Users</p>
            <p class="font-space font-bold">Manage Users</p>
            <p class="mt-1">View, edit, and manage platform users. Control roles, status, and permissions.</p>
        </div>
            <p class="font-semibold">Limited Access</p>
            <p class="text-sm mt-1">You can manage regular users only. Cannot manage Admins or Super Admins. Cannot access sensitive payment details.</p>
        </div>
    @endif

    @if($admin->isSuperAdmin())
        <details class="mb-5 bg-[#f3f3f3] p-4">
            <summary class="cursor-pointer font-space font-bold text-[#006a38]">Create User</summary>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                @csrf
                <input name="name" placeholder="Full name" class="input-field !py-2" required>
                <input type="email" name="email" placeholder="Email" class="input-field !py-2" required>
                <input type="password" name="password" placeholder="Password" class="input-field !py-2" required>
                <select name="role" class="input-field !py-2">
                    <option value="user">user</option>
                    <option value="admin">admin</option>
                    <option value="super_admin">super_admin</option>
                </select>
                <button class="btn-pill btn-pill-dark !px-4 !py-2">Create</button>
            </form>
        </details>
    @endif

    <div class="overflow-x-auto">
        <table class="editorial-table">
            <thead>
                <tr>
                    <th class="p-3 text-left">User</th>
                    <th class="p-3 text-left">Role</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Joined</th>
                    <th class="p-3 text-left">Listings</th>
                    <th class="p-3 text-left">Transactions</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $listingsCount = $user->products()->count();
                        $transactionsCount = $user->orders()->count();
                        $canManage = $admin->canManageUser($user);
                        $status = $user->account_status ?? 'active';
                    @endphp
                    <tr>
                        <td class="p-3">
                            <p class="font-semibold">{{ $user->name }}</p>
                            <p class="text-[#444746]">{{ $user->email }}</p>
                        </td>
                        <td class="p-3">
                            @if($admin->isSuperAdmin())
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="input-field !px-2 !py-1 text-xs">
                                        <option value="user" @selected($user->role === 'user')>user</option>
                                        <option value="admin" @selected($user->role === 'admin')>admin</option>
                                        <option value="super_admin" @selected($user->role === 'super_admin')>super_admin</option>
                                    </select>
                                    <button class="btn-pill btn-pill-dark !px-2 !py-1 text-xs">Save</button>
                                </form>
                            @else
                                <span class="status-chip {{ $user->role === 'user' ? 'status-neutral' : 'status-info' }}">{{ $user->role }}</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.users.status', $user) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="account_status" class="input-field !px-2 !py-1 text-xs" @disabled(!$canManage)>
                                    <option value="active" @selected($status === 'active')>active</option>
                                    <option value="suspended" @selected($status === 'suspended')>suspended</option>
                                    <option value="banned" @selected($status === 'banned')>banned</option>
                                </select>
                                <button class="btn-pill btn-pill-soft !px-2 !py-1 text-xs" @disabled(!$canManage)>Apply</button>
                            </form>
                        </td>
                        <td class="p-3">{{ $user->created_at->format('M j, Y') }}</td>
                        <td class="p-3">{{ $listingsCount }}</td>
                        <td class="p-3">{{ $transactionsCount }}</td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn-pill btn-pill-soft !px-2 !py-1 text-xs">View</a>
                                <form method="POST" action="{{ route('admin.users.resetPassword', $user) }}">
                                    @csrf
                                    <button class="btn-pill !px-2 !py-1 text-xs !border-amber-600 !text-amber-600 hover:!bg-amber-600 hover:!text-white" @disabled(!$canManage)>Reset</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.delete', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-pill !px-2 !py-1 text-xs !border-red-600 !text-red-600 hover:!bg-red-600 hover:!text-white" @disabled(!$canManage)
                                            onclick="return confirm('Delete user?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection

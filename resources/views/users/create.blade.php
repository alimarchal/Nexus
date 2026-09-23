{{--
    Add User uses the same screen as Edit User (users/edit.blade.php) so both
    look and behave the same: account, roles & office, extra permissions and a
    review modal before saving. The previous version is in git history.
--}}
@include('users.edit', [
    'user' => new \App\Models\User(['is_active' => 'Yes', 'is_super_admin' => 'No']),
    'userRoles' => [],
    'userPermissions' => [],
])

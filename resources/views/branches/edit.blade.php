<x-settings-form-page :title="'Edit Branch: '.$branch->code.' - '.$branch->name" subtitle="Settings → Branch → Branches" :back="route('branches.index')"
    :action="route('branches.update', $branch)" method="PUT" cardTitle="Branch details" cardSubtitle="Region → District → Branch, as in the bank's organogram."
    confirmTitle="Save changes to this branch?" confirmNote="Users and records linked to this branch will show the new details.">
    @include('branches._fields')
</x-settings-form-page>

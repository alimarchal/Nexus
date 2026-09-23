<x-settings-form-page title="Add Branch" subtitle="Settings → Branch → Branches" :back="route('branches.index')"
    :action="route('branches.store')" cardTitle="Branch details" cardSubtitle="Region → District → Branch, as in the bank's organogram."
    confirmTitle="Create this branch?" confirmButton="Create">
    @include('branches._fields')
</x-settings-form-page>

<x-settings-form-page :title="'Edit Region: '.$region->name" subtitle="Settings → Branch → Regions" :back="route('regions.index')"
    :action="route('regions.update', $region)" method="PUT" cardTitle="Region details" confirmTitle="Save changes to this region?"
    confirmNote="The new name shows on every branch, user and report linked to this region.">
    @include('regions._fields')
</x-settings-form-page>

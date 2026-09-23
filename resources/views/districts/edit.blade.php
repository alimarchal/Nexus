<x-settings-form-page :title="'Edit District: '.$district->name" subtitle="Settings → Branch → Districts" :back="route('districts.index')"
    :action="route('districts.update', $district)" method="PUT" cardTitle="District details" cardSubtitle="Every district belongs to one region."
    confirmTitle="Save changes to this district?" confirmNote="Branches in this district keep their own region; update them too if the district moves region.">
    @include('districts._fields')
</x-settings-form-page>

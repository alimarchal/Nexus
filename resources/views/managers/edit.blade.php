<x-settings-form-page :title="'Edit Manager: '.($manager->title ?: optional($manager->division)->name)" subtitle="Settings → User Module → Managers" :back="route('managers.index')"
    :action="route('managers.update', $manager)" method="PUT" cardTitle="Division manager" confirmTitle="Save changes to this manager?">
    @include('managers._fields')
</x-settings-form-page>

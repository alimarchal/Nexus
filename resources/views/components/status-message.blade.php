@if (session('deleted'))
    <div style="background-color: red; color: white; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('deleted') }}
    </div>
@endif
@if (session('success'))
    <div style="background-color: green; color: white; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        {{ session('success') }}
    </div>
@endif

<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ClinObserve maintenance</title><link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}"></head>
<body class="bg-light">
<main class="container py-5">
    <h1>ClinObserve</h1>
    <h2 class="h4 mb-4">{{ $page === 'migrations' ? 'Database maintenance' : 'Initial accounts and demo data' }}</h2>
    @if($output !== null)<pre class="border p-3 text-wrap" role="status">{{ $output }}</pre>@endif
    <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="{{ $csrfToken }}">
        <div class="col-12"><label class="form-label" for="maintenance_token">Maintenance token</label><input class="form-control" type="password" name="maintenance_token" id="maintenance_token" minlength="32" required autocomplete="off"></div>
        <div class="col-12"><label class="form-label" for="action">Action</label><select class="form-select" name="action" id="action">@foreach($actions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <fieldset class="col-12"><legend class="h5">New HOD account (initial setup or database reset)</legend><div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="hod_name">HOD name</label><input class="form-control" name="hod_name" id="hod_name" maxlength="255" autocomplete="name"></div>
            <div class="col-md-6"><label class="form-label" for="hod_email">HOD email</label><input class="form-control" type="email" name="hod_email" id="hod_email" maxlength="255" autocomplete="username"></div>
            <div class="col-md-6"><label class="form-label" for="hod_password">Temporary password</label><input class="form-control" type="password" name="hod_password" id="hod_password" minlength="12" maxlength="255" autocomplete="new-password"></div>
            <div class="col-md-6"><label class="form-label" for="hod_password_confirmation">Confirm temporary password</label><input class="form-control" type="password" name="hod_password_confirmation" id="hod_password_confirmation" autocomplete="new-password"></div>
        </div></fieldset>
        @if($page === 'migrations')
        <fieldset class="col-12"><legend class="h5 text-danger">Database reset only</legend>
            <p class="text-danger">Reset permanently deletes every table in the configured database, including accounts, sessions, observations and feedback. Export a backup in phpMyAdmin first. Existing uploaded files are not removed.</p>
            <div class="row g-3"><div class="col-md-6"><label class="form-label" for="database_name">Exact database name</label><input class="form-control" name="database_name" id="database_name" autocomplete="off"></div><div class="col-md-6"><label class="form-label" for="reset_confirmation">Type RESET DATABASE</label><input class="form-control" name="reset_confirmation" id="reset_confirmation" autocomplete="off"></div></div>
        </fieldset>
        @endif
        <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="confirm" value="yes" id="confirm" required><label class="form-check-label" for="confirm">I confirm this action on the configured database.</label></div></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Run selected action</button></div>
    </form>
</main>
</body>
</html>

@extends('installer.layout', [
    'title' => 'Environment Configuration',
    'subtitle' => 'Configure Your Application',
    'showSteps' => true,
    'currentStep' => 3
])

@section('content')
<h2 style="margin-bottom: 30px; color: #333;">Environment Configuration</h2>

<form method="POST" action="{{ route('installer.environment.save') }}">
    @csrf

    <!-- Application Settings -->
    <div style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 15px; color: #555;">Application Settings</h3>

        <div class="form-group">
            <label for="app_name">Application Name *</label>
            <input type="text" id="app_name" name="app_name" value="{{ old('app_name', 'Laravel Ecommerce') }}" required>
            <small>The name of your application</small>
        </div>

        <div class="form-group">
            <label for="app_url">Application URL *</label>
            <input type="url" id="app_url" name="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required>
            <small>The URL where your application will be accessible</small>
        </div>
    </div>

    <!-- Database Settings -->
    <div style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 15px; color: #555;">Database Settings</h3>

        <div class="form-group">
            <label for="db_connection">Database Type *</label>
            <select id="db_connection" name="db_connection" required onchange="toggleDatabaseFields(this.value)">
                <option value="mysql" {{ old('db_connection') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                <option value="sqlite" {{ old('db_connection') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                <option value="pgsql" {{ old('db_connection') == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
            </select>
        </div>

        <div id="mysql-fields">
            <div class="form-group">
                <label for="db_host">Database Host *</label>
                <input type="text" id="db_host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}">
                <small>Usually 127.0.0.1 or localhost</small>
            </div>

            <div class="form-group">
                <label for="db_port">Database Port *</label>
                <input type="text" id="db_port" name="db_port" value="{{ old('db_port', '3306') }}">
                <small>Default: 3306 for MySQL, 5432 for PostgreSQL</small>
            </div>

            <div class="form-group">
                <label for="db_database">Database Name *</label>
                <input type="text" id="db_database" name="db_database" value="{{ old('db_database', 'laravel_ecommerce') }}" required>
                <small>The name of your database</small>
            </div>

            <div class="form-group">
                <label for="db_username">Database Username *</label>
                <input type="text" id="db_username" name="db_username" value="{{ old('db_username', 'root') }}">
            </div>

            <div class="form-group">
                <label for="db_password">Database Password</label>
                <input type="password" id="db_password" name="db_password" value="{{ old('db_password') }}">
                <small>Leave blank if no password</small>
            </div>
        </div>

        <div id="sqlite-fields" style="display: none;">
            <div class="form-group">
                <label for="db_database_sqlite">Database File Path *</label>
                <input type="text" id="db_database_sqlite" name="db_database" value="{{ old('db_database', database_path('database.sqlite')) }}">
                <small>Full path to SQLite database file</small>
            </div>
        </div>
    </div>

    <div class="info-box">
        <strong>💡 Tip:</strong> Make sure the database exists before continuing. The installer will test the connection.
    </div>

    <div class="button-group">
        <a href="{{ route('installer.requirements') }}" class="btn btn-secondary">
            ← Back
        </a>
        <button type="submit" class="btn btn-primary" style="flex: 1;">
            Test & Continue →
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleDatabaseFields(type) {
    const mysqlFields = document.getElementById('mysql-fields');
    const sqliteFields = document.getElementById('sqlite-fields');

    if (type === 'sqlite') {
        mysqlFields.style.display = 'none';
        sqliteFields.style.display = 'block';
    } else {
        mysqlFields.style.display = 'block';
        sqliteFields.style.display = 'none';

        // Update port based on database type
        if (type === 'pgsql') {
            document.getElementById('db_port').value = '5432';
        } else {
            document.getElementById('db_port').value = '3306';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDatabaseFields(document.getElementById('db_connection').value);
});
</script>
@endpush

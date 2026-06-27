<h1>Payment Alert</h1>

<p><strong>Type:</strong> {{ $alert->type }}</p>
<p><strong>Severity:</strong> {{ $alert->severity }}</p>
<p><strong>Triggered:</strong> {{ $alert->triggered_at }}</p>

<pre>{{ json_encode($alert->context, JSON_PRETTY_PRINT) }}</pre>

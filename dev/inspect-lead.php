<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\\Models\\Lead;

$leadId = $argv[1] ?? null;
if (!$leadId) {
    fwrite(STDERR, "Usage: php dev/inspect-lead.php <lead-id>\n");
    exit(1);
}

$lead = Lead::find($leadId);
if (!$lead) {
    fwrite(STDERR, "Lead not found\n");
    exit(1);
}

$lmk = $lead->epc_data['certificate_number'] ?? null;
$recs = $lead->epc_recommendations ?? [];

echo json_encode([
    'lead_id' => $lead->id,
    'lmk' => $lmk,
    'recommendations_count' => is_array($recs) ? count($recs) : 0,
    'recommendations_titles' => array_values(array_map(fn($r) => $r['improvement-summary-text'] ?? $r['improvement-descr-text'] ?? null, $recs ?? [])),
], JSON_PRETTY_PRINT), "\n";



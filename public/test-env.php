<?php
require_once __DIR__ . '/../src/App/bootstrap.php';
use App\Env;

echo "<pre>";
echo "OPENROUTER_API_KEY: " . (Env::get('OPENROUTER_API_KEY') ? 'SET (' . strlen(Env::get('OPENROUTER_API_KEY')) . ' chars)' : 'NOT SET') . "\n";
echo "LLM_PROVIDER: " . Env::get('LLM_PROVIDER') . "\n";
echo "</pre>";
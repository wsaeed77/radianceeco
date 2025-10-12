<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class UpdateSetting extends Command
{
    protected $signature = 'setting:update {key} {value}';
    protected $description = 'Update a system setting';

    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');

        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            $this->error("Setting '{$key}' not found!");
            $this->info("\nAvailable settings:");
            Setting::all()->each(function ($s) {
                $this->info("  - {$s->key} (current: {$s->value})");
            });
            return 1;
        }

        $oldValue = $setting->value;
        Setting::set($key, $value, $setting->type);

        $this->info("✓ Setting updated successfully!");
        $this->info("  Key: {$setting->label} ({$key})");
        $this->info("  Old value: {$oldValue}");
        $this->info("  New value: {$value}");

        return 0;
    }
}

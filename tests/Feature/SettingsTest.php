<?php

use Sajdoko\TallPress\Models\Setting;
use Sajdoko\TallPress\Services\SettingsService;

beforeEach(function () {
    $this->settingsService = new SettingsService;
});

test('can create a setting', function () {
    $setting = Setting::create([
        'key' => 'test_setting',
        'value' => 'test_value',
        'type' => 'string',
        'group' => 'general',
    ]);

    expect($setting)->toBeInstanceOf(Setting::class)
        ->and($setting->key)->toBe('test_setting')
        ->and($setting->value)->toBe('test_value');
});

test('can get setting value with proper type casting', function () {
    Setting::create([
        'key' => 'test_boolean',
        'value' => '1',
        'type' => 'boolean',
    ]);

    Setting::create([
        'key' => 'test_integer',
        'value' => '42',
        'type' => 'integer',
    ]);

    $boolSetting = Setting::where('key', 'test_boolean')->first();
    $intSetting = Setting::where('key', 'test_integer')->first();

    expect($boolSetting->value)->toBeTrue()
        ->and($intSetting->value)->toBe(42);
});

test('can set and get setting via static methods', function () {
    Setting::set('test_key', 'test_value');

    expect(Setting::get('test_key'))->toBe('test_value');
});

test('can check if setting exists', function () {
    Setting::set('existing_key', 'value');

    expect(Setting::has('existing_key'))->toBeTrue()
        ->and(Setting::has('non_existing_key'))->toBeFalse();
});

test('can delete setting', function () {
    Setting::set('to_delete', 'value');

    expect(Setting::has('to_delete'))->toBeTrue();

    Setting::forget('to_delete');

    expect(Setting::has('to_delete'))->toBeFalse();
});

test('can get settings by group', function () {
    Setting::set('setting1', 'value1', 'string', 'group1');
    Setting::set('setting2', 'value2', 'string', 'group1');
    Setting::set('setting3', 'value3', 'string', 'group2');

    $group1Settings = Setting::getByGroup('group1');

    expect($group1Settings)->toHaveKey('setting1')
        ->toHaveKey('setting2')
        ->not->toHaveKey('setting3');
});

test('settings service falls back to config', function () {
    // Don't create any settings, should fall back to config
    $perPage = $this->settingsService->get('per_page');

    expect($perPage)->toBe(config('tallpress.per_page', 15));
});

test('settings service returns database value over config', function () {
    // Create a setting that differs from config
    Setting::set('per_page', 25, 'integer', 'general');

    $perPage = $this->settingsService->get('per_page');

    expect($perPage)->toBe(25);
});

test('can initialize default settings', function () {
    $this->settingsService->initializeDefaults();

    expect(Setting::has('per_page'))->toBeTrue()
        ->and(Setting::has('comments_enabled'))->toBeTrue()
        ->and(Setting::has('revisions_enabled'))->toBeTrue();
});

test('overwrites existing settings when initializing defaults', function () {
    Setting::set('per_page', 50, 'integer', 'general');

    $this->settingsService->initializeDefaults();

    // Should be reset to config default
    expect(Setting::get('per_page'))->toBe(config('tallpress.per_page', 15));
});

test('can set multiple settings at once', function () {
    $this->settingsService->setMany([
        'setting1' => ['value' => 'value1', 'type' => 'string', 'group' => 'test'],
        'setting2' => ['value' => true, 'type' => 'boolean', 'group' => 'test'],
        'setting3' => ['value' => 123, 'type' => 'integer', 'group' => 'test'],
    ]);

    expect(Setting::get('setting1'))->toBe('value1')
        ->and(Setting::get('setting2'))->toBeTrue()
        ->and(Setting::get('setting3'))->toBe(123);
});

test('can get all settings organized by group', function () {
    Setting::set('s1', 'v1', 'string', 'group1');
    Setting::set('s2', 'v2', 'string', 'group2');

    $all = $this->settingsService->all();

    expect($all)->toHaveKey('group1')
        ->toHaveKey('group2')
        ->and($all['group1'])->toHaveKey('s1')
        ->and($all['group2'])->toHaveKey('s2');
});

test('route prefix setting is used from database when available', function () {
    // Set custom route prefix in database
    Setting::set('route_prefix', 'articles', 'string', 'general');

    // Get setting via service
    $prefix = $this->settingsService->get('route_prefix');

    expect($prefix)->toBe('articles')
        ->and(tallpress_setting('route_prefix'))->toBe('articles');
});

test('route prefix falls back to config when not in database', function () {
    // Make sure no setting exists
    Setting::forget('route_prefix');

    // Should fall back to config value
    $prefix = $this->settingsService->get('route_prefix');

    expect($prefix)->toBe(config('tallpress.route_prefix', 'blog'));
});

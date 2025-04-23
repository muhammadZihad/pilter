<?php

use Illuminate\Support\Facades\File;
use Zihad\Pilter\Commands\MakeFilterCommand;

test('make:filter command exists', function () {
    // Test that the command class exists
    expect(class_exists(MakeFilterCommand::class))->toBeTrue();
});

test('filter stub file exists', function () {
    // Test that the stub file exists
    $stubPath = __DIR__ . '/../../stubs/Filter.stub';
    expect(file_exists($stubPath))->toBeTrue();
    
    // Test stub file content
    $stubContent = file_get_contents($stubPath);
    expect($stubContent)->toContain('namespace {{ namespace }}');
    expect($stubContent)->toContain('class {{ class }}Filter extends Filter');
});

test('command has correct signature', function () {
    $command = new MakeFilterCommand(app('files'));
    expect($command->getName())->toBe('make:filter');
});
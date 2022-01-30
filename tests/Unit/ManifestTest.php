<?php

use Innocenzi\Vite\Exceptions\ManifestNotFoundException;
use Innocenzi\Vite\Exceptions\NoSuchEntrypointException;
use Innocenzi\Vite\Manifest;

it('guesses the configuration name from the manifest path', function () {
    set_vite_config('config-name', [
        'build_path' => 'build/config-name',
    ]);

    expect(Manifest::guessConfigName(public_path('build/config-name/manifest.json')))
        ->toBe('config-name');
        
    set_vite_config('default', [
        'build_path' => 'build',
    ]);

    expect(Manifest::guessConfigName(public_path('build/manifest.json')))
        ->toBe('default');
});

it('does not return a guess when the build path is not defined', function () {
    expect(Manifest::guessConfigName(public_path('build/path-not-defined/manifest.json')))
        ->toBe(null);
});

it('throws a ManifestNotFoundException when the manifest does not exist', function () {
    get_manifest('unknown-manifest.json');
})->throws(ManifestNotFoundException::class);

it('finds a single entry', function () {
    expect(get_manifest('with-entry.json')->getEntries()->keys())
        ->toContain('resources/scripts/entry.ts')
        ->toHaveCount(1);
});

it('finds multiple entries', function () {
    expect(get_manifest('with-entries.json')->getEntries()->keys())
        ->toContain('resources/scripts/main.ts')
        ->toContain('resources/scripts/entry.ts')
        ->toHaveCount(2);
});

it('finds a css entry', function () {
    expect(get_manifest('with-css-entry.json')->getEntries()->keys())
        ->toContain('resources/css/tw.css')
        ->toHaveCount(1);
});

it('finds css imported from an entry', function () {
    $manifest = get_manifest('with-imported-css.json');

    expect($manifest->getEntries()->keys())
        ->toContain('resources/js/app.js')
        ->toHaveCount(1);

    expect($manifest->getEntries()->first()->getStyleTags())
        ->toHaveCount(1);
});

it('finds a entry with its file name', function () {
    expect(get_manifest('with-entries.json')->getEntry('entry'))
        ->src->toBe('resources/scripts/entry.ts')
        ->isEntry->toBeTrue();
});

it('finds a entry with its complete path name', function () {
    expect(get_manifest('with-entries.json')->getEntry('resources/scripts/entry.ts'))
        ->src->toBe('resources/scripts/entry.ts')
        ->isEntry->toBeTrue();
});

it('throws when trying to access an entry that does not exist', function () {
    get_manifest('with-entries.json')->getEntry('this-entry-does-not-exist');
})->throws(NoSuchEntrypointException::class);

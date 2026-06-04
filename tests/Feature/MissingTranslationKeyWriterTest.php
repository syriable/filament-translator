<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Syriable\Filament\Plugins\Translator\MissingTranslationKeyWriter;

beforeEach(function () {
    $this->langPath = sys_get_temp_dir() . '/ft-lang-' . Str::random(12);
    File::ensureDirectoryExists($this->langPath);
    app()->useLangPath($this->langPath);
});

afterEach(function () {
    File::deleteDirectory($this->langPath);
});

it('creates the file and full nested array path for a deep key', function () {
    $path = (new MissingTranslationKeyWriter())->write(
        'livewire/auth/login.form.components.actions.forgot-password.label',
        'Forgot Password',
        'en',
    );

    expect($path)->toBe($this->langPath . '/en/livewire/auth/login.php')
        ->and(File::exists($path))->toBeTrue()
        ->and(require $path)->toBe([
            'form' => [
                'components' => [
                    'actions' => [
                        'forgot-password' => [
                            'label' => 'Forgot Password',
                        ],
                    ],
                ],
            ],
        ]);
});

it('preserves existing translations when adding a new key', function () {
    $file = $this->langPath . '/en/livewire/auth/login.php';
    File::ensureDirectoryExists(dirname($file));
    File::put($file, "<?php\n\nreturn [\n    'form' => [\n        'heading' => 'Sign in',\n    ],\n];\n");

    (new MissingTranslationKeyWriter())->write('livewire/auth/login.form.components.email.label', 'Email', 'en');

    expect(require $file)->toBe([
        'form' => [
            'heading' => 'Sign in',
            'components' => [
                'email' => [
                    'label' => 'Email',
                ],
            ],
        ],
    ]);
});

it('never overwrites an existing value', function () {
    $writer = new MissingTranslationKeyWriter();
    $writer->write('app.title', 'Original', 'en');
    $writer->write('app.title', 'Changed', 'en');

    expect(require $this->langPath . '/en/app.php')->toBe(['title' => 'Original']);
});

it('writes short-array, 4-space-indented php', function () {
    (new MissingTranslationKeyWriter())->write('app.nav.home', 'Home', 'en');

    expect(File::get($this->langPath . '/en/app.php'))
        ->toBe("<?php\n\nreturn [\n    'nav' => [\n        'home' => 'Home',\n    ],\n];\n");
});

it('escapes single quotes and backslashes in values', function () {
    (new MissingTranslationKeyWriter())->write('app.msg', "It's a \\ test", 'en');

    expect(require $this->langPath . '/en/app.php')->toBe(['msg' => "It's a \\ test"]);
});

it('writes into the locale-specific directory', function () {
    (new MissingTranslationKeyWriter())->write('app.title', 'Titre', 'fr');

    expect(File::exists($this->langPath . '/fr/app.php'))->toBeTrue()
        ->and(require $this->langPath . '/fr/app.php')->toBe(['title' => 'Titre']);
});

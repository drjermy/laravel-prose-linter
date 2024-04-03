<?php

namespace Beyondcode\LaravelProseLinter\Linter;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| ContentLinter
|--------------------------------------------------------------------------
|
| In order to Lint content that is passed by string, we write a file,
| copy it to the LaravelProseLinter directory, perform the Lint on the
| file and then ensure that everything is deleted afterwards.
|
*/

class ContentLinter extends Vale
{
    public function lint($content, $identifier = 'lint'): array
    {
        $fileName = "{$identifier}.html";
        $storagePath = "tmp/{$fileName}";
        Storage::disk('local')->put($storagePath, $content);

        $copyPath = $this->createLintableCopy($fileName);
        $lints = $this->lintFile($copyPath, $identifier);
        $this->deleteLintableCopy();

        Storage::disk('local')->delete($storagePath);

        return $lints ?? [];
    }

    public function createLintableCopy($templateKey): string
    {
        if (! is_dir($this->valePath.'/tmp')) {
            File::makeDirectory($this->valePath.'/tmp');
        }

        $tmpPath = storage_path('app/tmp/').$templateKey;

        $templateCopyPath = $this->valePath."/tmp/{$templateKey}";
        File::copy($tmpPath, $templateCopyPath);

        return $templateCopyPath;
    }

    public function deleteLintableCopy(): void
    {
        File::deleteDirectory($this->valePath.'/tmp');
    }
}

<?php declare(strict_types=1);

namespace App\Modules\Admin\Presenters;

use Nette\Application\Responses\FileResponse;
use Tracy\Debugger;

final class ExceptionPresenter extends AbstractBasePresenter
{
    /**
     * Renders html exception from log directory with provided filename.
     *
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault(string $filename): void
    {
        $file = Debugger::$logDirectory . DIRECTORY_SEPARATOR . $filename;
        $this->sendResponse(new FileResponse($file, $filename, 'text/html', false));
    }
}

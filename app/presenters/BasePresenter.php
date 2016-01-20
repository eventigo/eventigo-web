<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Utils\DateTime;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var \Kdyby\Translation\Translator @inject */
	public $translator;


	protected function createTemplate()
	{
		$template = parent::createTemplate();

		$template->addFilter('datetime', function (DateTime $a, DateTime $b = null) {
			$result = $a->format('j. n. ');
			if ($b && $a != $b) {
				$result .= '&nbsp;&ndash;&nbsp;' . $b->format('j. n. ');
			}
			return $result . $b->format('Y');
		});

		return $template;
	}
}

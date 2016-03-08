<?php

namespace App\Modules\Newsletter\Presenters;

use App\Modules\Core\Model\EventModel;
use App\Modules\Core\Model\TagModel;
use App\Modules\Core\Model\UserNewsletterModel;
use App\Modules\Core\Model\UserTagModel;
use App\Modules\Core\Presenters\BasePresenter;
use App\Modules\Newsletter\Components\Newsletter\NewsletterFactory;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Json;


class NewsletterPresenter extends BasePresenter
{
	/** @var NewsletterFactory @inject */
	public $newsletterFactory;

	/** @var TagModel @inject */
	public $tagModel;

	/** @var EventModel @inject */
	public $eventModel;

	/** @var UserNewsletterModel @inject */
	public $userNewsletterModel;

	/** @var UserTagModel @inject */
	public $userTagModel;

	/** @var ActiveRow */
	private $userNewsletter;


	/**
	 * @param string $hash
	 */
	public function actionDefault($hash)
	{
		$this->userNewsletter = $this->userNewsletterModel->getAll()->where([
			'hash' => $hash,
		])->fetch();
	}


	public function createComponentNewsletter()
	{
		if ($this->userNewsletter->variables) {
			$tagsCodes = Json::decode($this->userNewsletter->variables, Json::FORCE_ARRAY);
			$tagsIds = $this->tagModel->getAll()
				->where(['code' => $tagsCodes])
				->fetchPairs(NULL, 'id');
		} else {
			$tagsIds = $this->userNewsletter->user->related('users_tags')->fetchPairs(NULL, 'tag_id');
		}

		$previousUserNewsletter = $this->userNewsletterModel->getAll()
			->where('user_id', $this->userNewsletter->user_id)
			->where('hash <> ?', $this->userNewsletter->hash)
			->order('sent DESC')->fetch();

		$from = isset($previousUserNewsletter->sent) ? $previousUserNewsletter->sent : NULL;
		$to = $this->userNewsletter->sent ?: NULL;

		$events = $this->eventModel->getAllWithDates($tagsIds, $from, $to);
		return $this->newsletterFactory->create($this->userNewsletter->newsletter, $events);
	}
}
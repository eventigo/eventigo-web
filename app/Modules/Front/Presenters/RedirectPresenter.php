<?php declare(strict_types=1);

namespace App\Modules\Front\Presenters;

use App\Modules\Core\Model\EventModel;
use App\Modules\Core\Model\EventRedirectModel;
use App\Modules\Core\Presenters\AbstractBasePresenter;
use Nette\Http\Url;

final class RedirectPresenter extends AbstractBasePresenter
{
    /**
     * @var EventModel
     */
    private $eventModel;

    /**
     * @var EventRedirectModel
     */
    private $eventRedirectModel;

    public function __construct(EventRedirectModel $eventRedirectModel, EventModel $eventModel)
    {
        $this->eventRedirectModel = $eventRedirectModel;
        $this->eventModel = $eventModel;
    }

    public function renderDefault(string $url): void
    {
        // Find event with same url
        $events = $this->eventModel->getAll()->where('origin_url', $url)->fetchAll();

        foreach ($events as $event) {
            // Count the redirect
            $this->eventRedirectModel->insert([
                'event_id' => $event->id,
                'user_id' => $this->getUser()->isLoggedIn() ? $this->getUser()->getId() : null,
            ]);
        }

        $this->redirectUrl(new Url($url));
    }
}

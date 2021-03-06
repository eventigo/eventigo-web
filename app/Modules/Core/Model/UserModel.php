<?php declare(strict_types=1);

namespace App\Modules\Core\Model;

use App\Modules\Front\Model\Exceptions\Subscription\EmailExistsException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\IRow;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;
use Nette\Utils\Random;

final class UserModel extends AbstractBaseModel
{
    /**
     * Login type.
     *
     * @var string
     */
    public const SUBSCRIPTION_LOGIN = 'subscription';

    /**
     * Login Type.
     *
     * @var string
     */
    public const ADMIN_LOGIN = 'admin';

    /**
     * @var string
     */
    protected const TABLE_NAME = 'users';

    /**
     * @var int
     */
    private const TOKEN_LENGTH = 64;

    /**
     * @throws EmailExistsException
     * @throws \PDOException
     */
    public function subscribe(string $email): ?IRow
    {
        if ($email) {
            if ($this->emailExists($email)) {
                throw new EmailExistsException;
            }

            // Create user
            /** @var ActiveRow $user */
            $user = $this->insert([
                'email' => $email,
                'token' => $this->generateToken(),
            ]);

            return $user;
        }
    }

    public function emailExists(string $email): bool
    {
        return (bool) $this->getUserByEmail($email);
    }

    public function getUserByEmail(string $email): ?IRow
    {
        $user = $this->getAll()
            ->where('email', $email)
            ->fetch();

        return $user ?: null;
    }

    /**
     * Hash the password.
     */
    public function hashAndEncrypt(string $password): string
    {
        return Passwords::hash($password);
    }

    public function generateToken(): string
    {
        do {
            $token = Random::generate(self::TOKEN_LENGTH);
        } while ($this->getAll()->where(['token' => $token])->fetch());

        return $token;
    }

    /**
     * @return bool|IRow
     */
    public function findByFacebookId(string $facebookId)
    {
        return $this->getAll()->where('facebook_id', $facebookId)->fetch();
    }

    public function signInViaFacebook(ArrayHash $me): IRow
    {
        if (isset($me->email) && $user = $this->getAll()->where('email', $me->email)->fetch()) {
            $this->getAll()->wherePrimary($user->id)->update([
                'facebook_id' => $me->id,
            ]);

            return $this->findByFacebookId($me->id);
        }

        return $this->insert([
            'email' => $me->email ?? null,
            'facebook_id' => $me->id,
            'token' => $this->generateToken(),
        ]);
    }

    public function updateFacebook(ArrayHash $me, string $token): IRow
    {
        $user = $this->getAll()->where('facebook_id', $me->id)->fetch();

        $this->getAll()->wherePrimary($user->id)->update([
            'facebook_token' => $token,
            'firstname' => $user->firstname ?: $me->first_name,
            'fullname' => $user->fullname ?: $me->name,
        ]);

        return $this->findByFacebookId($me->id);
    }

    /**
     * Get user token (hash).
     *
     * @return false|mixed
     */
    public function getUserToken(int $userId)
    {
        return $this->getAll()->wherePrimary($userId)->fetchField('token');
    }

    public function showAbroadEvents(?int $userId): bool
    {
        if ($userId) {
            $user = $this->getAll()->wherePrimary($userId)->fetch();

            return (bool) $user->abroad_events;
        }

        return true;
    }
}

<?php
namespace lic;

final class LicenseExistedException extends \Exception {}

final class LicData
{
    public $userCount;
    public $monthCount;
    public $userName;
    public $type;

    public static function toArray(LicData $value): array
    {
        return get_object_vars($value);
    }

    public static function fromArray(array $value): LicData
    {
        $rv = new LicData();
        array2ClassProp($value, $rv);
        return $rv;
    }
}

final class UserLic
{
    public $state;
    public $expired;
    public $userCount;
    public $lastUserCountCheck;
}

final class ValidateLic
{
    public $result;
    public $errors;

    public function __construct(bool $result = false)
    {
        $this->result = $result;
    }
}

interface iLicenseDataAccess
{
    function getLicByUsername(string $username);
    function save(string $username, UserLic $data);
    function getUserCount($username, \amoCrm\actions\auth\AmoCrmAuth $auth): int;
}

abstract class License
{
    protected $da;
    private $trialDays;

    public function __construct(iLicenseDataAccess $da, int $trialDays)
    {
        $this->da = $da;
        $this->trialDays = $trialDays;
    }

    public function openTrial(string $username)
    {
        if (!$this->getInfo($username, null)) {
            $userLic = new UserLic();
            $userLic->state = 'trial';
            $userLic->expired = (new \DateTime())->getTimestamp() + ($this->trialDays * 24 * 3600);
            $userLic->lastUserCountCheck = 0;
            $this->setState($username, $userLic);
        } else {
          throw new LicenseExistedException();
        }
    }

    public function setActiveLic(LicData $data): ValidateLic
    {
        $currentLic = $this->getInfo($data->userName, null);
        $validate = $this->validateData($currentLic, $data);
        if ($validate->result) {
            $userLic = new UserLic();
            $userLic->state = 'active';
            $userLic->userCount = $data->userCount;
            $monthCount = $data->monthCount;
            $di = new \DateInterval("P${monthCount}M");
            $userLic->expired = (new \DateTime())->add($di)->getTimestamp();
            $userLic->lastUserCountCheck = 0;
            $this->setState($data->userName, $userLic);
        }
        return $validate;
    }

    public function prolongationLic(LicData $data): ValidateLic
    {
        $currentLic = $this->getInfo($data->userName, null);
        if ($currentLic && in_array($currentLic->state, ['trial', 'expired'])) {
            $data->userCount = $currentLic->userCount;
            return $this->setActiveLic($data);
        } elseif($currentLic->state === 'active') {
            $validate = $this->validateData($currentLic, $data);
            if ($validate->result) {
                $monthCount = $data->monthCount;
                $di = new \DateInterval("P${monthCount}M");
                $currentLic->expired = (new \DateTime())->setTimestamp($currentLic->expired)->add($di)->getTimestamp();
                $currentLic->lastUserCountCheck = 0;
                $this->setState($data->userName, $currentLic);
            }
            return $validate;
        }
        return new ValidateLic();
    }

    public function addUsers2Lic(LicData $data): ValidateLic
    {
        $currentLic = $this->getInfo($data->userName, null);
        if ($currentLic && in_array($currentLic->state, ['active', 'moreUsers'])) {
            $validate = $this->validateData($currentLic, $data);
            if ($validate->result) {
                $currentLic->state = 'active';
                $currentLic->userCount = $currentLic->userCount + $data->userCount;
                $currentLic->lastUserCountCheck = 0;
                $this->setState($data->userName, $currentLic);
            }
            return $validate;
        }
        return new ValidateLic();
    }

    public function stopLic(LicData $data)
    {
        $currentLic = $this->getInfo($data->userName, null);
        $currentLic->state = 'trial';
        $currentLic->expired = (new \DateTime())->getTimestamp();
        $currentLic->lastUserCountCheck = 0;
        $this->setState($data->userName, $currentLic);
    }

    public function check(string $username, \amoCrm\actions\auth\AmoCrmAuth $auth): bool
    {
        $licData = $this->getInfo($username, $auth);
        return $licData && in_array($licData->state, ['active', 'trial']);
    }

    public function getInfo(string $username, $auth)
    {
        $changed = false;
        $licData = $this->da->getLicByUsername($username);

        if ($licData) {
            $dt = (new \DateTime())->getTimestamp();

            if ($dt - $licData->expired >= 0) {
                $licData->state = 'expired';
                $changed = true;
            }

            if ($licData->state != 'trial' && $auth && $dt - $licData->lastUserCountCheck >= LIC_USER_COUNT_CHECK_PERIOD) {
                $licData->state = ($this->da->getUserCount($username, $auth) > $licData->userCount ? 'moreUsers' : $licData->state);
                $licData->lastUserCountCheck = $dt;
                $changed = true;
            }

            if ($changed) {
                $this->setState($username, $licData);
            }
        }

        return $licData;
    }

    public abstract function validateData(UserLic $currentLic, LicData $data): ValidateLic;

    private function setState(string $username, UserLic $data)
    {
        $this->da->save($username, $data);
    }
}

final class AppLicense extends License
{
    public function __construct(iLicenseDataAccess $da, int $trialDays)
    {
        parent::__construct($da, $trialDays);
    }

    public function validateData(UserLic $currentLic, LicData $data): ValidateLic
    {
        $rv = new ValidateLic();
        switch (mb_strtolower($data->type)) {
            case 'new':
                $rv->result = ($data->monthCount >= MIN_MONTH_COUNT) && ($data->userCount * ONE_USER_MONTH_PRICE >= MIN_ALL_PRICE_MONTH);
                break;
            case 'prolong':
                $rv->result = $data->monthCount >= MIN_MONTH_COUNT && $data->userCount === $currentLic->userCount;
                break;
            case 'addusers':
                $rv->result = $data->userCount > 0;
                break;
        }
        $rv->errors = ['minMonth' => MIN_MONTH_COUNT, 'minMonthPrice' => MIN_ALL_PRICE_MONTH];
        return $rv;
    }
}

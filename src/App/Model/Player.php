<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;
use App\Model\PlayerAtTeam;

class Player extends \App\Model
{
    const STATE_ACTIVE = 'active';
    const STATE_INACTIVE = 'inactive';
    const STATE_DELETED = 'deleted';

    const SEX_MALE = 'male';
    const SEX_FEMALE = 'female';

    protected static $stateList = [
        self::STATE_ACTIVE,
        self::STATE_INACTIVE,
        self::STATE_DELETED,
    ];

    protected static $sexList = [
        self::SEX_MALE,
        self::SEX_FEMALE,
    ];

    protected static $fields = ["id", "first_name", "last_name", "birth_date", "created_at", "email", "phone", "sex", "state", "nationality", "gdpr_consent"];

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public static function create($firstName, $lastName, $sex, $email = null, $birthDate = null, $phone = null, $state = self::STATE_ACTIVE, $nationality = null, $gdprConsent = false)
    {
        $i = new self();
        $i->setFirstName($firstName);
        $i->setLastName($lastName);
        $i->setSex($sex);
        $i->setState($state);
        $i->setEmail($email);
        $i->setPhone($phone);
        $i->setBirthDate($birthDate);
        $i->setNationality($nationality);
        $i->setGdprConsent($gdprConsent);
        $i->setCreatedAt(date("Y-m-d H:i:s", time()));

        return $i;
    }

    public static function extendedJoins(&$joins = [], $alias = "") {
        $joins["[>]player_at_team(player_at_team___player)"] = ["id" => "player_id"];
        PlayerAtTeam::extendedJoins($joins, "player_at_team___player");
        parent::extendedJoins($joins, $alias);
    }

    public function getExtendedData(&$loaded = array()) {
        return parent::getExtendedData($loaded);
    }

}

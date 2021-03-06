<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;
use App\Model\User;

class UserHasPrivilege extends \App\Model
{
    const PRIVILEGE_ADMIN = 'admin';
    const PRIVILEGE_EDIT = 'edit';
    const PRIVILEGE_VIEW = 'view';

    const ENTITY_TEAM = 'team';
    const ENTITY_HIGHSCHOOL = 'highschool';

    protected static $privilegeList = [
        self::PRIVILEGE_ADMIN,
        self::PRIVILEGE_EDIT,
        self::PRIVILEGE_VIEW,
    ];

    protected static $entityList = [
        self::ENTITY_TEAM,
        self::ENTITY_HIGHSCHOOL,
    ];

    protected static $fields = ["id", "user_id", "privilege", "entity", "entity_id"];

    public static function create($userId, $privilege, $entity = null, $entityId = null)
    {
        if (empty($entity)) {
            $entityId = null;
            $entity = null;
        }
        $i = new self();
        $i->setUserId($userId);
        $i->setPrivilege($privilege);
        $i->setEntity($entity);
        $i->setEntityId($entityId);

        return $i;
    }

    public function canBeViewedBy(\App\Model\User $user)
    {
        foreach ($user->privileges() as $privilege) {
            if ($privilege->getPrivilege() == self::PRIVILEGE_ADMIN) {
                return true;
            }
            if ($privilege->getEntity() == $this->getEntity() && $privilege->getEntityId() == $this->getEntityId()) {
                return true;
            }
        }
        return false;
    }

    public function privilegeToString()
    {
        if ($this->getPrivilege() == 'admin') {
            return $this->getPrivilege();
        } else {
            return $this->getPrivilege() . ':' . $this->getEntity() . ':' . $this->getEntityId();
        }
    }

    protected function onSaveValidation()
    {
        if ($this->isNew() && self::exists([
            "user_id" => $this->getUserId(),
            "privilege" => $this->getPrivilege(),
            "entity" => $this->getEntity(),
            "entity_id" => $this->getEntityId()
        ])) {
            throw new Duplicate("Privilege already exists");
        }
    }

    public function isAdmin() {
        return $this->getPrivilege() == static::PRIVILEGE_ADMIN;
    }
}

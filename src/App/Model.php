<?php
namespace App;
use App\Exception\Http\Http500;
use App\Exception\WrongParam;
use App\Exception\Database\TreatAsCreated;

abstract class Model
{
    protected static $table = null;
    protected static $fields = [];
    protected $data = [];

    protected $enrichCondtions = [];

    public static function exists($select)
    {
        $select = self::enrichSelect($select);
        return Context::getContainer()->db->has(static::table(), $select);
    }

    public static function count($select)
    {
        $select = self::enrichSelect($select);
        return Context::getContainer()->db->count(static::table(), $select);
    }

    public static function load($select)
    {
        $select = self::enrichSelect($select);
        $out = [];
        foreach (Context::getContainer()->db->select(static::table(), static::$fields, $select) as $data) {
            $out[] = static::fromArray($data);
        }

        return $out;
    }

    final public static function enrichSelect($select)
    {
        return [
            "AND" => array_merge(static::getExplicitCondtions(), $select)
        ];
    }
    protected static function getExplicitCondtions()
    {
        return [];
    }

    protected static function fromArray($data)
    {
        $i = new static();
        $i->data = $data;
        return $i;
    }

    protected function __construct()
    {
    }

    public function __call($name, $args)
    {
        $field = preg_replace("/^(get|set)_/", "", preg_replace_callback("/[A-Z]+/", function ($matches) {
            return "_" . strtolower($matches[0]);
        }, $name));
        $upperCaseField = (preg_replace("/^(get|set)/", "", $name));

        if (strpos($name, "get") === 0 && in_array($field, static::$fields)) {
            return isset($this->data[$field]) ? $this->data[$field] : null;
        } elseif (strpos($name, "set") === 0 && in_array($field, static::$fields) && count($args) == 1) {
            if (property_exists(__CLASS__, $upperCaseField . "List")) {
                if (!array_key_exists($args[0], static::${$upperCaseField . "List"})) {
                    throw new Http500("Wrong ENUM parameter: " . $args[0]);
                }
            }
            $this->data[$field] = $args[0];
        }
    }

    public function isNew()
    {
        return empty($this->getId());
    }

    public function getData()
    {
        return $this->data;
    }

    public function save()
    {
        $this->onSaveValidation();

        $db = Context::getContainer()->db;

        $data = $this->data;
        unset($data["id"]);

        if (!empty($this->data['id'])) {
            $db->update(
                static::table(),
                $data,
                ["id" => $this->getId()]
            );
        } else {
            $id = $db->insert(
                static::table(),
                $data
            );
            $this->setId($id);
        }
        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2]);
        }
    }

    public function delete()
    {
        if ($this->isNew()) {
            return;
        }
        $db = Context::getContainer()->db;
        $db->delete(static::table(), ['id' => $this->getId()]);

        if (!empty($db->error()[1])) {
            throw new \App\Exception\Database($db->error()[2]);
        }
    }

    protected function onSaveValidation()
    {
        return true;
    }

    final protected static function table()
    {
        if (empty(static::$table)) {
            $class = explode("\\", get_called_class());
            $className = array_pop($class);
            static::$table = trim(preg_replace_callback("/[A-Z]+/", function ($matches) {
                return "_" . strtolower($matches[0]);
            }, $className), '_');
        }
        return static::$table;
    }

    final protected function setId($id)
    {
        $this->data["id"] = $id;
    }
}
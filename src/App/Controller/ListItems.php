<?php
namespace App\Controller;

use App\Model\UserHasPrivilege;
use App\Model\User as UserModel;
use App\Exception\Http\Http400;

class ListItems extends \App\Common
{

    public static $listable = [
        "player", "team", "player_at_team",
        "roster", "player_at_roster", "tournament",
        "season", "tournament_belongs_to_league_and_division",
        "division", "league", "user"
    ];

    public function listAll(\Slim\Http\Request $request, $response, $args)
    {
        list($type) = $request->requireParams(["type"]);
        $type = strtolower($type);
        if (!in_array($type, self::$listable)) {
            throw new Http400("Item not listable");
        }
        $filter = null;
        $prefilter = $request->getParam("filter", null);
        $extend = (bool)$request->getParam("extend", false);

        $limit = $request->getParam("limit", null);
        $offset = $request->getParam("offset", null);

        if (!empty($prefilter)) {
            $filter = [];
            array_walk($prefilter, function ($value, $key) use (&$filter) {
                $filter[urldecode($key)] = $value;
            });
        }

        $model = "\\App\\Model\\" . ucfirst(\App\Model::camelcaseNotation($type));
        $data = $model::load($filter, $limit, $offset);

        return $this->container->view->render(
            $response,
            ['status' => 'OK', 'data' => array_map(function ($item) use ($extend) {
                if ($extend) {
                    return $item->getExtendedData();
                } else {
                    return $item->getData();
                }
            }, $data), 'filter' => $filter],
            200
        );
    }
}

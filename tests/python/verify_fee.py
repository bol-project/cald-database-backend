#!/usr/bin/python
from lib import call

season_id = 9

admin_data = call('admin/fee?season_id=%s' % season_id)
fees = admin_data['data']['fee']
duplicities = admin_data['data']['duplicate_players']
total = 0
for team in fees:
    team_id = fees[team]['id']
    team_data = call('team/%s/season/%u/fee'
                     % (team_id, season_id))

    admin_missing = [
        player['name'] for player
        in fees[team]["players"]
        if player not in team_data["data"]["fee"][team]["players"]
    ]
    team_missing = [
        player["name"] for player
        in team_data["data"]["fee"][team]["players"]
        if player not in [p["name"] for p in fees[team]["players"]]
    ]

    admin_duplicities = [
        duplicity for duplicity
        in duplicities
        if team in duplicities[duplicity]["teams"]
    ]
    team_duplicates = [
        duplicity for duplicity in team_data["data"]["duplicate_players"]
    ]

    print "%20s [#%3s] %s %s" % (
        team, fees[team]["id"], len(fees[team]["players"]), fees[team]["fee"]
    )

    total += fees[team]["fee"]

    ok = True
    if (len(set(team_duplicates) - set(admin_duplicities)) > 0):
        ok = False
        print "%20s team is missing duplicities: %s" % (
            team, ", ".join(set(admin_duplicities) - set(team_duplicates))
        )
        ok = False
        print "%20s admin is missing duplicities: %s" % (
            team, ", ".join(set(team_duplicates) - set(admin_duplicities))
        )
        print team_data["data"]["duplicate_players"]
        print duplicities[duplicity]["teams"]
    if (len(admin_missing) > 0):
        ok = False
        print "%20s admin is missing: %s" % (team, ", ".join(admin_missing))
    if (len(team_missing) > 0):
        ok = False
        print "%20s team is missing:  %s" % (team, ", ".join(team_missing))
    if (fees[team]["fee"] != team_data["data"]["fee"][team]["fee"]):
        ok = False
        print ('%20s price differs:  %s != %s'
               % (fees[team]["fee"], team_data["data"]["fee"][team]["fee"])
               )
    if (ok):
        print "%20s OK" % team
print "total: %s" % total

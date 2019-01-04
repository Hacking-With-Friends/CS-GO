## To fetch the submodules
# git submodule update --init --recursive
## To update them recursively
# git submodule update --remote --merge

from api import vote
from spiderWeb import spiderWeb

"""
	"test@hvornum.se": {
        "displayname": "test@hvornum.se",
        "domain": "hvornum.se",
        "id": "test",
        "messages": {},
        "participants": {
            "hvornum.se": {
                "jill": {}
            }
        }
    }
"""

_sessions = {}
_teams = {}
_sockets = {}

try:
	__builtins__.__dict__['sessions'] = _sessions
	__builtins__.__dict__['teams'] = _teams
	__builtins__.__dict__['sockets'] = _sockets
except:
	__builtins__['sessions'] = _sessions
	__builtins__['teams'] = _teams
	__builtins__['sockets'] = _sockets

server = spiderWeb.server({'api:vote' : vote})
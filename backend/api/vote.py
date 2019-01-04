from urllib.parse import urlencode, quote_plus
from base64 import b64encode as benc
from os import urandom
from struct import pack
from time import time
import pickle

def gen_UID():
	return benc(bytes(str(time()), 'UTF-8') + urandom(16)).decode('UTF-8')

class protocol:
	def parse(data, headers, fileno, addr):
		#{"team":1,"session":"123123"}

		if 'cmd' in data:
			if data['cmd'] == 'save':
				with open('state.pic', 'wb') as state:
					pickle.dump({'sessions' : sessions, 'teams' : teams}, state)

			elif data['cmd'] == 'load':
				with open('state.pic', 'rb') as state:
					tmp = pickle.load(state)

				try:
					__builtins__.__dict__['sessions'] = tmp['sessions']
					__builtins__.__dict__['teams'] = tmp['teams']
				except:
					__builtins__['sessions'] = tmp['sessions']
					__builtins__['teams'] = tmp['teams']

			return {'state' : 'success', 'cmd' : data['cmd']}

		if 'admin' in data and 'ninjat_create' in data:
			team_1 = gen_UID()
			team_2 = gen_UID()
			session = gen_UID()

			sessions[session] = {1 : team_1, 2 : team_2, 'turn' : 1, 'bans' : [], 'picks' : []}
			teams[team_1] = {'sessions' : [session], 'sockets' : [], 'name' : data['team_1']}
			teams[team_2] = {'sessions' : [session], 'sockets' : [], 'name' : data['team_2']}

			return {'state' : 'success', 'teams' : {'1' : quote_plus(team_1), '2' : quote_plus(team_2)}, 'session' : quote_plus(session), "team_1" : data["team_1"], "team_2" : data["team_2"]}

		elif 'admin' in data and 'ninjat_view' in data:
			return {'state' : 'success', 'session' : data['session'],
						'bans' : sessions[data['session']]['bans'],
						'picks' : sessions[data['session']]['picks'],
						'teams' : {'team_1' : teams[sessions[data['session']][1]]['name'], 'team_2' : teams[sessions[data['session']][2]]['name']}}

		elif 'admin' in data and 'ninjat_list' in data:
			payload = {}
			print(sessions)
			for session in sessions:
				payload[quote_plus(session)] = {'team_1' : {'name' : teams[sessions[session][1]]['name'], 'id' : sessions[session][1]}, 'team_2' : {'name' : teams[sessions[session][2]]['name'], 'id' : sessions[session][1]}}
			return {'state' : 'success', 'all_sessions' : payload}

		if 'team' in data and 'session' in data:
			if not data['team'] in teams:
				print('Team not in teams:')
				print(teams)
				return {'state' : 'failed', 'msg' : 'No such team: ' + data['team']}
			if not data['session'] in sessions:
				print(sessions)
				return {'state' : 'failed', 'msg' : 'No such session: ' + data['session']}

			if not 'ban' in data or 'pick' in data:
				sockets[data['team']] = fileno
				return {'state' : 'success', 'msg' : 'Welcome team {}'.format(data['team'])}
			else:
				if 'ban' in data: action = 'ban'
				else: action = 'pick'

				for team_index in [1,2]:
					team_id = sessions[data['session']][team_index]
					print('Team id:', team_id, team_id in sockets)
					if not team_id in sockets:
						return {'state' : 'failed', 'action' : action, 'msg' : 'The other team isn\'t here yet.'}

				if sessions[data['session']][sessions[data['session']]['turn']] == data['team']:
					if data[action] in sessions[data['session']]['bans']:
						return {'state' : 'failed', 'action' : action, 'msg' : 'That map is already banned.'}

					if sessions[data['session']]['turn'] == 1: sessions[data['session']]['turn'] = 2
					else: sessions[data['session']]['turn'] = 1

					for team_index in [1,2]:
						team_id = sessions[data['session']][team_index]
						team_fileno = sockets[team_id]
						clients[team_fileno]['socket'].ws_send({'state' : 'success', 'action' : action, 'msg' : 'Map was banned!', 'map' : data[action]})

					sessions[data['session']]['bans'].append(data[action])
					return {'state' : 'success', 'action' : action, 'msg' : 'It was your turn! good! :D'}
				else:
					return {'state' : 'failed', 'action' : action, 'msg' : 'Not your turn to vote.'}
<html>
	<haed>
		<style type="text/css">

		</style>
		<script type="text/javascript">
			let session = '<?= $_GET["session"]; ?>';
			let socket = new WebSocket("wss://voting.hvornum.se/");

			window.onload = function() {
				if(session != '') {
					socket.addEventListener('open', function (event) {
						socket.send(JSON.stringify({"admin" : "yes", "ninjat_view" : "ofcourse", "session" : session}));
					});

					socket.addEventListener('message', function (event) {
						console.log('Message from server: ', event.data);
						response = JSON.parse(event.data);
						console.log(response);
						let content = '';

						content += '<h3>Teams:</h3>';
						content += response['teams']["team_1"] + ' vs ' + response['teams']["team_2"] + '<br><br>';

						let maps = {'de_dust2' : false,
									'de_overpass' : false,
									'de_cache' : false,
									'de_nuke' : false,
									'de_train' : false,
									'de_mirage' : false,
									'de_inferno' : false};

						content += '<h3>Bans:</h3>';
						for(let bans_index in response['bans']) {
							maps[response['bans'][bans_index]] = true;
							content += response['bans'][bans_index] + '<br>';
						}

						content += '<h3>Picks:</h3>';
						for(let map_name in maps) {
							if(!maps[map_name])
								content += map_name + '<br>';
						}

						//content += '<h3>Picks:</h3>';
						//for(let pick_index in response['picks']) {
						//	content += response['picks'][pick_index] + '<br>';
						//}

						document.getElementById('content').innerHTML = content;
					});
				} else {
					socket.addEventListener('open', function (event) {
						socket.send(JSON.stringify({"admin" : "yes", "ninjat_list" : "ofcourse"}));
					});

					socket.addEventListener('message', function (event) {
						console.log('Message from server: ', event.data);
						response = JSON.parse(event.data);
						console.log(response);
						let content = '';

						if(typeof response['all_sessions'] !== 'undefined') {
							for(let session_index in response['all_sessions']) {
								let team_1_name = response['all_sessions'][session_index]['team_1']['name'];
								let team_2_name = response['all_sessions'][session_index]['team_2']['name'];
								let votes = response['all_sessions'][session_index]['votes'];

								content += '<a href="http://mapvote.ninjat.se/ninjas_admin.php?session='+session_index+'">' + team_1_name + ' vs ' + team_2_name + '</a> (Votes: '+(votes['bans'].length+votes['picks'].length)+') ['+votes['picks'].join(' -> ')+']<br>';
							}
							document.getElementById('content').innerHTML = content;
						}
					});
				}

				document.getElementById('cmd_send').addEventListener('click', function() {
					socket.send(JSON.stringify({"cmd" : document.getElementById('cmd').value}));
				});

				document.getElementById('send').addEventListener('click', function() {
					socket.send(JSON.stringify({"admin" : "yes", "ninjat_create" : "ofcourse", "session" : session, "team_1" : document.getElementById('team_1').value, "team_2" : document.getElementById('team_2').value, 'map_pool' : document.getElementById('map_pool').value}));
					

					socket.addEventListener('message', function (event) {
						console.log('Message from server: ', event.data);
						response = JSON.parse(event.data);

						let data = '';

						data += 'Ni möter '+response['team_2']+' och **ni börjar** att rösta när andra laget kommer in.<br>';
						data += ' * Det går **INTE** att ångra en ban/pick.<br>';
						data += ' * Varannat lag röstar och ordningen är: Ban, Ban, Pick, Pick, Ban, Ban *(och sista kartan blir automatiskt avgörande kartan)*<br>';
						data += '<br>';
						data += response['team_1'] + ': http://mapvote.ninjat.se/?session='+response['session']+'&team_id=' + response['teams']['1'];

						data += '<br><br><br><br><br><br>';

						data += 'Ni möter '+response['team_1']+' och **dom börjar** att rösta.<br>';
						data += '<br>';
						data += ' * Det går **INTE** att ångra en ban/pick.<br>';
						data += ' * Varannat lag röstar och ordningen är: Ban, Ban, Pick, Pick, Ban, Ban *(och sista kartan blir automatiskt avgörande kartan)*<br>';
						data += '<br>';
						data += response['team_2'] + ': http://mapvote.ninjat.se/?session='+response['session']+'&team_id=' + response['teams']['2'];
						
						data += '<br><br><br><br><br><br>Admins: http://mapvote.ninjat.se/ninjas_admin.php?session='+response['session'];

						document.getElementById('content').innerHTML = data;
					});
				})
			}

		</script>
	</haed>
	<body>
		<input type="text" name="team_1" id="team_1" placeholder="Team #1 name"><br>
		<input type="text" name="team_2" id="team_2" placeholder="Team #2 name"><br>
		<input type="text" name="map_pool" id="map_pool" value="5v5-3"><br>
		<input type="submit" id="send" value="Generate links"><br>
		<br>
		<input type="text" name="cmd" id="cmd" placeholder="Custom command..."><br>
		<input type="submit" id="cmd_send" value="Send command"><br>
		<br>

		<div id="content">
		</div>
	</body>
</html>
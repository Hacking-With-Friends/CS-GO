<html>
	<haed>
		<style type="text/css">
			@font-face {
				font-family: CS;
				src: url('/cs_regular.ttf');
			}

			:root {
				--blue: #62CFEE;
				--pink: #F92472;
				--green: #A6E22C;
				--yellow: #E7DB74;
				--orange: #f60;
				--moreorange: #66D9EF;
				--teal: #66D9EF;
				--darkish: #74705D;
				--dark: #2a2a2a;
			}

			body {
				font-family: 'CS';
				background-image: url('./icons/backdrop.jpg');
				background-size: cover;
				color: #FFFFFF;
			}

			#content {
				display: flex;
			}

				.spacer {
					flex: 1 auto;
				}

				.picked {
					flex: 0 0 160px;
				}

				.spacer_full {
					flex: 1 1;
				}

				#maps {
					flex: 0 0 300px;
					flex-flow: row wrap;
				}

					.bans {
						display: flex;
					}

					.map {
						cursor: pointer;
						flex: 1 auto;
						width: 120px;
						height: 80px;
						text-align: center;
						line-height: 180px;
						margin: 5px;
					}

					.picked_map {
						margin-bottom: 20px;
					}

					.picked_map:nth-child(even) {
						margin-left: 130px;
					}

					.picked_map:nth-child(odd) {
						margin-right: 130px;
					}
					
						#de_inferno {
							background-image: url('./icons/de_inferno.jpg');
							background-size: cover;
						}
						#de_inferno::after {
							content: "de_inferno";
						}
						#de_cache {
							background-image: url('./icons/de_cache.png');
							background-size: cover;
						}
						#de_cache::after {
							content: "de_cache";
						}
						#de_dust2 {
							background-image: url('./icons/de_dust2.png');
							background-size: cover;
						}
						#de_dust2::after {
							content: "de_dust2";
						}
						#de_mirage {
							background-image: url('./icons/de_mirage.png');
							background-size: cover;
						}
						#de_mirage::after {
							content: "de_mirage";
						}
						#de_overpass {
							background-image: url('./icons/de_overpass.png');
							background-size: cover;
						}
						#de_overpass::after {
							content: "de_overpass";
						}
						#de_nuke {
							background-image: url('./icons/de_nuke.jpg');
							background-size: cover;
						}
						#de_nuke::after {
							content: "de_nuke";
						}
						#de_train {
							background-image: url('./icons/de_train.png');
							background-size: cover;
						}
						#de_train::after {
							content: "de_train";
						}

			.newRow {
				margin-right: calc(100% - 100px);
			}

			#orders {
				text-align: center;
			}

				.slot {
					width: 40px;
					height: 40px;
					border: 1px solid #FF0000;
				}

			.banned {
				position: relative;
				cursor: default;
			}

			.banned:after {
				position: absolute;
				content:"";
				top:0;
				left:0;
				width:100%;
				height:100%;
				opacity: .5;
				background-color: red;
			}

			#notification {
				width: 450px;
				position: absolute;
				left: 50%;
				top: 120px;
				margin-left: -225px;
				margin-top: -90px;
				background-color: #272822;
				border: 1px solid var(--blue);
				text-align: center;
			}
				.error {
					border: 1px solid var(--pink) !important;
					background-color: var(--orange) !important;
				}
				.error > h3 {
					color: var(--pink) !important;
				}
				#notification > h3 {
					font-family: 'LuckGuy', cursive;
					font-size: 25px;
				}

		</style>
		<script type="text/javascript">
			let session = '<?= $_GET['session']; ?>';
			let team_id = '<?= $_GET['team_id']; ?>';
			let bans = 0;
			var timers = {};
			let mouse = {};
			let sprites = {};

			let socket = new WebSocket("wss://voting.hvornum.se/");

			socket.addEventListener('open', function (event) {
				socket.send(JSON.stringify({"team" : team_id, "session" : session}));
			});

			socket.addEventListener('message', function (event) {
				console.log('Message from server: ', event.data);
				response = JSON.parse(event.data);

				if(typeof response['state'] !== 'undefined' && response['state'] == 'failed') {
					notify("Error", response['msg'], true);
					return
				}

				if(typeof response['action'] !== 'undefined') {
					if(response['action'] == 'ban' && response['state'] == 'success') {
						if(typeof response['map'] !== 'undefined') {
							bans += 1;
							let m = document.createElement('div');
							m.id = response['map'];
							m.classList = 'map picked_map';
							document.getElementById('orders').appendChild(m);

							let clicked = document.getElementById(response['map'])
							clicked.classList = clicked.classList + ' banned';

							// Remove the event listener for 'click' events.
							var new_element = clicked.cloneNode(true);
							clicked.parentNode.replaceChild(new_element, clicked);
						}
					}
				}
			});

			function notify(title, content, error=false, clear_popup) {
				if(clear_popup) {
					let popup = document.getElementById('popup');
					if(popup)
						popup.remove();
				}
				notification = document.createElement('div');
				notification.id = 'notification';
				notification.innerHTML = '<h3 id="popup_title">'+title+'</h3>';
				notification.innerHTML += '<p>'+content+'</p>';
				document.body.appendChild(notification);
				if (error) {
					document.getElementById('popup_title').style.color = '#FD971F';
					notification.setAttribute('class', 'error');
				}
				setTimer('clear_popup', function() {
					let notification = document.getElementById('notification');
					if(notification)
						notification.remove();
					clearTimer('clear_popup');
				}, 5000);
			}

			function clearTimer(name) {
				if(timers[name] !== undefined) {
					window.clearInterval(timers[name]);
					return true;
				}
				return false;
			}
			function setTimer(name, func, time=10) {
				timers[name] = setInterval(func, time);
			}
			function destroy(obj) {
				if(obj)
					obj.remove();
			}
			function populateChildren(parent, objects) {
				Object.entries(objects).forEach(([index, val]) => {
					let key = Object.keys(val)[0];
					let o = document.createElement(key);
					if(typeof val[key]["styles"] !== 'undefined') {
						Object.entries(val[key]["styles"]).forEach(([s, sval]) => {
							o.style[s] = sval;
						});
					}
					Object.entries(val[key]).forEach(([property, propval]) => {
						if(property == "objects" || property == "styles")
							return;
						if(property == "innerHTML")
							o.innerHTML += propval;
						else
							o.setAttribute(property, propval);
					});
					if(typeof val[key]["objects"] !== 'undefined') {
						populateChildren(o, val[key]["objects"]);
					}
					parent.appendChild(o);
				});
			}
			function destoryPopup(e) {
				if(Date.now() - sprites['popup'].time < 100)
					return;
				let height = sprites['popup'].obj.scrollHeight;
				let width = sprites['popup'].obj.scrollWidth;
				let x_pos = sprites['popup'].obj.offsetLeft;
				let y_pos = sprites['popup'].obj.offsetTop;
				let x = e.clientX;
				let y = e.clientY;
				if ((x < x_pos || x > (x_pos+width)) || (y < y_pos || y > (y_pos+height))) {
					destroy(sprites['popup'].obj);
					clearTimeout(timers['closePopup']);
					delete timers['closePopup'];
				}
			}
			function showPopup(struct) {
				if(typeof mouse['close_popup'] !== 'undefined') {
					delete mouse['close_popup'];
				}
				if(typeof sprites['popup'] !== 'undefined') {
					destroy(sprites['popup'].obj);
					delete sprites['popup'];
				}
				let d = document.createElement('div');
				d.id = 'popup';
				Object.entries(struct["styles"]).forEach(([key, val]) => {
					d.style[key] = val;
				});
				if(typeof struct["objects"] !== 'undefined')
					populateChildren(d, struct["objects"]);
				document.body.appendChild(d);
				mouse["close_popup"] = destoryPopup;
				sprites['popup'] = {"obj" : d, "time" : Date.now()};
			}

			function ban(map_name) {
				if (bans >= 6)
					return;

				socket.send(JSON.stringify({"ban": map_name, "session": session, "team": team_id}));
			}

			function pick(map_name) {

			}

			function getDivChildren(node) {
				let children = new Array();
				for(let child in node.childNodes) {
					if(node.childNodes[child].nodeType == 1) {
						children.push(node.childNodes[child]);
					}
				}
				return children;
			}

			window.onload = function() {
				let divs = getDivChildren(document.getElementById('maps'));
				for(let div_index in divs) {
					if(divs[div_index].classList == 'bans' || divs[div_index].classList == 'picks') {
						let maps = getDivChildren(divs[div_index]);
						for(let map_index in maps) {
							let map_name = maps[map_index].id;
							if(map_name.substring(0, 3) == 'de_') {
								console.log('Found map: ' + map_name);
								if(divs[div_index].classList == 'bans') {
									maps[map_index].addEventListener('click', function() {
										if(bans >= 6)
											return;

										ban(map_name);
									})
								}
							}
						}
					}
				}
			}
		</script>
	</haed>
	<body>
		<div id="content">
			<div class="spacer"></div>
			<div id="maps">
				<div class="bans">
					<div id="de_inferno" class="map"></div>
					<div id="de_cache" class="map"></div>
					<div id="de_dust2" class="map"></div>
					<div id="de_mirage" class="map"></div>
					<div id="de_overpass" class="map"></div>
					<div id="de_nuke" class="map"></div>
					<div id="de_train" class="map"></div>
				</div>
				<!--<div class="picks"> </div>-->
			</div>
			<div class="spacer"></div>
		</div>
		<br>
		<br>
		<br>
		<div id="content">
			<div class="spacer_full"></div>
			<div id="picked">
				<div id="orders">
					<h3>Ban Order</h3>
				</div>
			</div>
			<div class="spacer_full"></div>
		</div>
	</body>
</html>
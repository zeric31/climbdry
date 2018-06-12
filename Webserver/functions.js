

////////////////////////////////////////////////////////FUNCTIONS FOR CONTROLS/////////////////////////////////////////////////////////
	
	//PIN CONTROL
	function create_pin_control(options){
		L.Control.Pin = L.Control.extend({
			options: {
				position: ''
			},
			onAdd: function(){
				this.container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom disabled');
				this.link = L.DomUtil.create('a', '', this.container);
				this.link.href = "#";
				this.link.onclick = function(){return false;};
				this.link.style.backgroundImage = "url(icons/marker.png)";
				this.link.style.backgroundSize = "20px 20px";
				
				this.container.onclick = function(e){
					e.stopPropagation();
					if(L.DomUtil.hasClass(this, 'disabled')){
						L.DomUtil.removeClass(this, 'disabled');
						var position = map.getCenter();
						var radius = min_screen_size()/2;
						ref_marker = create_ref_marker(position);
						ref_marker.addTo(map);
						circle = new L.circle(position, radius);
						circle.addTo(map);

						get_offset(position).then(function(offset){
							set_ddm(offset);
						});
						
						circle_controls = create_circle_controls({position: 'topleft'});
						circle_controls.addTo(map);
						search_control = create_search_control({position: 'topleft'});
						search_control.addTo(map);
					}
					else{
						L.DomUtil.addClass(this, 'disabled');
						map.removeLayer(ref_marker);
						map.removeLayer(circle);
						map.removeControl(circle_controls);
						map.removeControl(search_control);
						map.removeControl(ddm_begin);
						map.removeControl(ddm_end);
					}
				};
				return this.container;
			}
		});
		return new L.Control.Pin(options);
	}

	//CIRCLE CONTROLS
	function create_circle_controls(options){
		L.Control.Circle = L.Control.extend({
			options: {
				position: '' 
			},
			onAdd: function () {
				this.container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom zoom');
				preventLongPressMenu(this.container);
				this.container.onclick = function(e){
					e.stopPropagation();
				};
				
				var plus = L.DomUtil.create('a', '', this.container);
				plus.href = "#";
				plus.onclick = function(){return false;};
				plus.style.backgroundImage = "url(icons/enlarge.png)";
				plus.style.backgroundSize = "25px 25px";
				
				var minus = L.DomUtil.create('a', '', this.container);
				minus.href = "#";
				minus.onclick = function(){return false;};
				minus.style.backgroundImage = "url(icons/shrink.png)";
				minus.style.backgroundSize = "25px 25px";
				
				var radius = circle.getRadius();
				var timeout, interval, screen;
				L.DomEvent.addListener(plus,isTouchEnabled ? 'touchstart' : 'mousedown', function(){
					screen = min_screen_size()/2;
					var initial = Math.max(radius/20,screen/20);
					incrementRadius(initial);
					var flag = new Date().getTime();
					timeout = setTimeout(function() {
						interval = setInterval(function() {
							var flag2 = new Date().getTime();
							var diff=(flag2-flag-300)/1000*initial;
							incrementRadius(diff);
						}, 10);
					}, 300);
				});
				L.DomEvent.addListener(minus,isTouchEnabled ? 'touchstart' : 'mousedown', function(){
					screen = min_screen_size()/2;
					var initial = -Math.max(radius/20,screen/20);
					if(circle.getRadius()+initial>screen/20) incrementRadius(initial);
					var flag = new Date().getTime();
					timeout = setTimeout(function() {
						interval = setInterval(function() {
							var flag2 = new Date().getTime();
							var diff=(flag2-flag-300)/1000*initial;
							if(circle.getRadius()+diff>screen/20) incrementRadius(diff);
						}, 10);    
					}, 300);
				});
				
				function incrementRadius(diff){ circle.setRadius(circle.getRadius()+diff) };
				
				function preventLongPressMenu(node) {
					node.ontouchstart = absorbEvent_;
					node.ontouchmove = absorbEvent_;
					node.ontouchend = absorbEvent_;
					node.ontouchcancel = absorbEvent_;
				}
				
				function absorbEvent_(event) {
					var e = event || window.event;
					e.preventDefault && e.preventDefault();
					e.stopPropagation && e.stopPropagation();
					e.cancelBubble = true;
					e.returnValue = false;
					return false;
				}
				
				function clearTimers(){
					clearTimeout(timeout);
					clearInterval(interval);
				}
				
				['mouseup', 'touchend', 'mouseleave'].forEach(function(e){
					L.DomEvent.addListener(plus,e,clearTimers);
					L.DomEvent.addListener(minus,e,clearTimers);
				});
				
				return this.container;
			},
			onRemove: function () {
				L.DomUtil.remove(this.container);
			}
		});
		return new L.Control.Circle(options);
	}

	//DROPDOWNMENU
	function create_dropdownmenu(options){
		L.Control.DropDownMenu = L.Control.extend({
			options: {
				position: '',
				id: '', //From or To
				title: ''
			},
			initialize: function (options) {
				L.Util.setOptions(this, options);
			},
			onAdd: function () {
				this.container = L.DomUtil.create('div', 'search-container');
				this.form = L.DomUtil.create('form', this.options.id, this.container);
				this.select = L.DomUtil.create('select', 'bigger', this.form);
				this.select.setAttribute('id',this.options.id); 
				this.option = L.DomUtil.create('option', '', this.select);
				this.option.setAttribute('selected','');
				this.option.setAttribute('disabled','');
				//option.setAttribute('hidden','');
				this.option.innerHTML = this.options.title;
				if(!document.querySelector('#offset')){
					g = document.createElement('div');
					g.setAttribute('id', 'offset');
					g.setAttribute('style','display: none;');
					this.form.appendChild(g);
				}
				L.DomEvent.disableClickPropagation(this.container);
				return this.container;
			},
			onRemove: function () {
				L.DomUtil.remove(this.container);
			}
		});
		
		return new L.Control.DropDownMenu(options);
	}
	
	
	function create_search_control(options){
		L.Control.Search = L.Control.extend({
			options: {
				position: '' 
			},
			onAdd: function () {
				this.container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom zoom');
				this.search = L.DomUtil.create('a', '', this.container);
				this.search.href = "#";
				this.search.onclick = function(){return false;};
				this.search.style.backgroundImage = "url(icons/search.png)";
				this.search.style.backgroundSize = "20px 20px";
				
				this.container.onclick = function(e){
					e.stopPropagation();					
					var coord_arr = intersection();
					var offset = $('#offset').html();
					
					var ddm_values = get_ddm();	
					if(ddm_values[0]<0) ddm_values[0]=0;
					if(ddm_values[1]<0) ddm_values[1]=120;
					
					if(ddm_values[0]>=ddm_values[1]){
						alert("Please select a valid time range");
						return;
					}

					var loader = $('#loader');
					loader.addClass("show");
					
					get_offset(circle.getLatLng()).then(function(offset){	
						var request = $.ajax({
							url: "./predict_multiple.php",
							type: "POST",
							datatype: "json",
							data: {ids: coord_arr.join(), t_min : ddm_values[0], t_max: ddm_values[1], offset: offset}
						});

						request.done(function (response, textStatus, jqXHR){
							document.getElementById('tbc').setAttribute('href','#ranking');
							$("#ranking .table").html("");
							if(response.length>0){
								$("#ranking .table").append("\
									<table class='pure-table'>\
										<thead>\
											<tr>\
												<th rowspan='2' style='text-align:center;'>Name</th>\
												<th rowspan='2' style='width: 3em; text-align:center;'>Dist. <span style='font-size:x-small;'>(km)</span></th>\
												<th colspan='2'style='width: 6em; text-align:center;'>Precipitation</th>\
											</tr>\
											<tr>\
												<th style='width: 3em; text-align:center;'>Daily</th>\
												<th style='width: 3em; text-align:center;'>Tot. <span style='font-size:x-small;'>(mm)</span></th>\
											</tr>\
										</thead>\
									<tbody>\
									</tbody>\
									</table>\
								");
								var max = response.pop();
								var counter = 1;
								var circle_coord = circle.getLatLng();
								var id, name, total_rainfall, marker_coord, distance;
								for (var spot in response) {
									var rain_arr = [];
									id = response[spot][0]["id"];
									name = response[spot][0]["name"];
									total_rainfall = parseFloat(response[spot][0]["total"]).toFixed(1);
									marker_coord = markers.getLayer(markers_ref[id]).getLatLng();
									distance = Math.round(marker_coord.distanceTo(circle_coord)/1000);
									for (var day in response[spot][1]) rain_arr.push(response[spot][1][day]["y"]);
									if(counter%2) $("tbody").append("\
										<tr>\
											<td style='padding:0.5em;'><a href='#"+id+"'>"+name+"</a></td>\
											<td style='width: 3em; text-align:center;'>"+distance+"</td>\
											<td style='width: 3em; text-align:center;'><span class='bar"+id+"' style='display: none;'>"+rain_arr.join()+"</span></td>\
											<td style='width: 3em; text-align:center;'>"+total_rainfall+"</td>\
										<tr>\
									");
									else $("tbody").append("\
										<tr class='pure-table-odd'>\
											<td style='padding:0.5em;'><a href='#"+id+"'>"+name+"</a></td>\
											<td style='width: 3em; text-align:center;'>"+distance+"</td>\
											<td style='width: 3em; text-align:center;'><span class='bar"+id+"' style='display: none;'>"+rain_arr.join()+"</span></td>\
											<td style='width: 3em; text-align:center;'>"+total_rainfall+"</td>\
										<tr>\
									");
									$(".bar"+id).peity("bar", { max : max });
									counter++;
								}
							}
							else $("#ranking .table").append("No climbing spots in selected area.");
							loader.removeClass("show");
							location.hash = '#ranking';
						});
					});
				};
				return this.container;
			},
			onRemove: function () {
				L.DomUtil.remove(this.container);
			}
		});
		return new L.Control.Search(options);
	}


	
////////////////////////////////////////////////////////ADDITIONAL FUNCTIONS FOR CONTROLS/////////////////////////////////////////////////////////
	
	//IMPLEMENTS AND ADDS A REFERENCE MARKER
	function create_ref_marker(position){
		var marker = new L.marker(position, {draggable:'true'});
		marker.on('dragend', function(event){
			var new_coord = event.target.getLatLng();
			marker.setLatLng(new_coord,{draggable:'true'});
			if(map.hasLayer(circle)) circle.setLatLng(new_coord);
			get_offset(new_coord).then(function(offset){
				compare_offset(offset);
			});
		});
		marker.on('drag', function(e){
			if(map.hasLayer(circle)) circle.setLatLng(e.latlng);	
		});
		return marker;
	}

	//RETURNS MARKERS INSIDE CIRCLE
	function intersection(){
		var circle_coord = circle.getLatLng();
		var radius = circle.getRadius();
		var coord_arr = [];
		var marker_coord;
		markers.eachLayer(function (layer) {
			marker_coord = layer.getLatLng();
			if (marker_coord.distanceTo(circle_coord) <= radius) coord_arr.push(layer.feature.id);
		});
		return coord_arr;
	}
	
	//ACTUAL TABLE IN MYSQL
	function actual_table() {
		return fetch("./actual_table.php").then(function(response){
			return response.json();
		});
	}
	
	//ACTUAL TABLE IN MYSQL IN JS DATE FORMAT
	function datestring(){
		return actual_table().then(function(date){
			var year = date.substring(0, 4);
			var month = date.substring(4,6);
			var day = date.substring(6,8);
			var hours = date.substring(8,10);
			return year + "-" + month + "-" + day +"T" + hours +":00:00";
		});		
	}
	
	function get_ddm_indexes(){
		var ddm_indexes = [];
		var ddm_from_div = document.getElementById("From");
		var ddm_to_div = document.getElementById("To");
		ddm_indexes.push(ddm_from_div.selectedIndex);
		ddm_indexes.push(ddm_to_div.selectedIndex);
		return ddm_indexes;
	}
	
	function get_ddm(){
		var ddm = [-1,-1];
		var ddm_from_div = document.getElementById("From");
		var ddm_to_div = document.getElementById("To");
		if(!!ddm_from_div && ddm_from_div.selectedIndex > 0) ddm[0] = Number(ddm_from_div.options[ddm_from_div.selectedIndex].value);
		if(!!ddm_to_div && ddm_to_div.selectedIndex > 0) ddm[1] = Number(ddm_to_div.options[ddm_to_div.selectedIndex].value);
		return ddm;
	}
	
	function set_ddm(offset, ddm_from_index, ddm_to_index){
		datestring().then(function(result){
			var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
			ddm_begin.addTo(map);
			ddm_end.addTo(map);
			$("#offset").html(offset);
			for (i = 0; i <= 120; i = i + 6) {
				var d = new Date(result);
				d.setHours(d.getHours() + Math.floor(offset));
				d.setMinutes(d.getMinutes() + (offset - Math.floor(offset)) * 60);							
				d.setHours(d.getHours() + i);
				d2 = new Date(d);
				if(d2.getMinutes() == "0") $("form.To > select, form.From > select").append("<option value='"+ i +"'>" + weekday[d2.getDay()] + " " + d2.getHours() + ":00</option>");
				else $("form.To > select, form.From > select").append("<option value='"+ i +"'>" + weekday[d2.getDay()] + " " + d2.getHours() + ":" + d2.getMinutes() +"</option>");
			}
			document.getElementById("From").selectedIndex = ddm_from_index; 
			document.getElementById("To").selectedIndex = ddm_to_index; 
		});
	}

	//GETS LOCAL TIME DIFFERENCE COMPARED WITH UTC
	function get_offset(position){
		return $.ajax({
			url: "https://maps.googleapis.com/maps/api/timezone/json",
			type: "get",
			datatype: "json",
			data: {location: position.lat + "," + position.lng, timestamp : Math.floor(Date.now() / 1000), key : KEY}
			}).then(function(response){
				if (response['status'] != 'OK'){
					alert("Don't center on the sea.");
					return null; 
				}
				return (response["dstOffset"]+response["rawOffset"])/3600;
			});
	}
	
	//COMPARES OFFSET AND UPDATES OFFSET IF NEEDED
	function compare_offset(new_offset){
		if(!!document.querySelector('#offset')){
			var current_offset = $('#offset').html();
			if(current_offset != new_offset){
				var ddm_indexes = get_ddm_indexes();
				map.removeControl(ddm_begin);
				map.removeControl(ddm_end);
				set_ddm(new_offset, ddm_indexes[0], ddm_indexes[1]);
			}
		}
	}
	
	//MINIMAL SCREEN LENGTH
	function min_screen_size(){
		var y = map.getSize().y;
		var x = map.getSize().x;
		var maxMetersx = map.containerPointToLatLng([0, 0]).distanceTo( map.containerPointToLatLng([x,0]));
		var maxMetersy = map.containerPointToLatLng([0, 0]).distanceTo( map.containerPointToLatLng([0,y]));
		return Math.min(maxMetersx,maxMetersy);
	}

	

////////////////////////////////////////////////////////REST OF FUNCTIONS/////////////////////////////////////////////////////////

	//ADDS ALL SPOTS AS MARKERS
	function load_spots(url){
		var markers = new L.MarkerClusterGroup({showCoverageOnHover: false, animate:false, spiderfyDistanceMultiplier: 4});
	
		L.AwesomeMarkers.Icon.prototype.options.prefix = 'fa';
		var redMarker = L.AwesomeMarkers.icon({
			icon: '',
			markerColor: 'orange',
			shadowSize: [0, 0]
		});
		
		$.ajax({
			url: url,
			dataType: "json",
			cache: false,
			success: function(data){
				L.geoJson(data ,{
					pointToLayer: function(feature,latlng){
						var marker = L.marker(latlng,{
							icon: redMarker,
							id: feature.id
						});
						marker.on('click', function(){
							if(location.hash.substring(1) == feature.id) show_overlay(feature.id);
							location.hash = '#'+feature.id;
						});
						return marker;
					}
				}).addTo(markers);
				
				markers.eachLayer(function (layer) {
					markers_ref[layer.feature.id] = markers.getLayerId(layer);
				});
			}
		});
		return markers;
	}
	
	//MOVES REF MARKER (on map click)
	function move_ref_marker(e) {
		if(map.hasLayer(ref_marker)){
			var position = e.latlng;
			ref_marker.setLatLng(position);	
			circle.setLatLng(position);
			get_offset(position).then(function(offset){
				compare_offset(offset);
			});
		}
	};
	
	//SIDEBAR OPENER
	function show_overlay(hash){
		var fired = markers.getLayer(markers_ref[hash]);
		if(!fired) return;
		var position = fired.getLatLng();
		var loader = $('#loader');
		if(loader.hasClass("show")){
			loader.removeClass("show");
		}
		loader.addClass("show");
		var ddm_values = get_ddm();
		if(ddm_values[0]<0 || location.hash.indexOf('&') !== -1) ddm_values[0]=0;
		if(ddm_values[1]<0 || location.hash.indexOf('&') !== -1) ddm_values[1]=120;

		if(ddm_values[0]>=ddm_values[1]){
			alert("Please select a valid time range");
			return;
		}
		get_offset(position).then(function(offset){	
			var request = $.ajax({
				url: "./chart.php",
				type: "POST",
				datatype: "json",
				data: {id: hash, t_min : ddm_values[0], t_max: ddm_values[1], offset: offset}
			});

			request.done(function (response, textStatus, jqXHR){
				if(response.length>0){	
					sidebar.open('details',response[0]['name']);
					adaptTitle();
					
					//var ctx = document.getElementById("myChart");
					//if (myBarChart) myBarChart.destroy();
					$("#details .sidebar-text").html("");
					$("#details .sidebar-text").append('<canvas id="myChart" style="height: 70%; width: 100%;"></canvas>');

					var ctx = document.getElementById("myChart");
					myBarChart = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: response[1],
							datasets: [{
								data: response[2],
								backgroundColor: 'rgba(46, 138, 219, 0.5)'
							}]
						},
						options: {
							legend: { display: false },
							title: {
								display: true,
								text: 'Forecasted Cumulative Precipitation (in mm)',
								fontFamily: 'Quicksand',
								fontStyle: 'normal',
								fontSize: '12',
								lineHeight: '3'
							},
							tooltips:{
								callbacks:{
									title: function(tooltipItem){
										return this._data.labels[tooltipItem[0].index];
									},
									label: function(tooltipItems, data) { 
										return tooltipItems.yLabel + 'mm';
									}
								}
							},
							scales: {
								xAxes: [{
									ticks:{
										autoSkip: false,
										callback: function(value, index, values){
											var length = values.length;
											if(index==0){
												var split = value.split(" - ");
												var first = split[0].substring(0, 3);
												var second = split[1].substring(0, 3);
												return first == second ? first : '';
											}
											else{
												var previous = values[index-1];
												var split = previous.split(" - ");
												var first = split[0].substring(0, 3);
												var second = split[1].substring(0, 3);
												return first !== second ? second : '';
											}
											return '';
										}
									}
								}],
								yAxes: [{
									ticks: {
										min: 0
									}
								}]
							}
						}
					});
					var ddm_values2 = get_ddm();
					if(ddm_values2[0] > 0 || (ddm_values2[1] !== -1 && ddm_values2[1] !== 120)) $("#details .sidebar-text").append('\
						<div style="overflow:auto">\
							<div style="display:inline-block; float:left;">\
								<a href="#" onclick="panandzoom('+ markers_ref[hash] +');return false;">\
									<span class="caption font" style="padding-left: 10px;">Show on map</span>\
								</a>\
							</div>\
							<div style="display:inline-block; float:right">\
								<a href="#'+ (location.hash.indexOf('&') == -1 ? hash+'&all' : hash) +'">\
									<span class="caption font" style="padding-right: 10px;">'+ (location.hash.indexOf('&') == -1 ? 'Complete forecast' : 'Limited forecast') +'</span>\
								</a>\
							</div>\
						</div>');
					else $("#details .sidebar-text").append('\
						<div style="overflow:auto">\
							<div style="display:inline-block; float:left;">\
								<a href="#" onclick="panandzoom('+ markers_ref[hash] +');return false;">\
									<span class="caption font" style="padding-left: 10px;">Show on map</span>\
								</a>\
							</div>\
						</div>');
					$("#details .sidebar-text").append('<div class="text font">The cumulated precipitation from '+response[1][0].split(" - ")[0]+' to '+response[1].slice(-1)[0].split(" - ")[1]+' amounts to <span style="font-weight:bold;">'+parseFloat(response[0]["total"]).toFixed(1)+'mm</span></div>');
				}
				loader.removeClass("show");
			});
		});
	}
	
	function panandzoom(id){
		var fired = markers.getLayer(id);
		if(!fired) return;
		sidebar.close();
		var position = fired.getLatLng();
		markers.zoomToShowLayer(fired, function(){
			map.panTo(position);
			fired.bounce(1);
		});	
	}

	//ADAPTS TITLE FONT-SIZE TO SCREEN RESIZE
	function adaptTitle(){
		var divWidth = $('#details').width();	
		var text = $('#details .title');
		text.css('display', 'none');
		var fontSize = 24.2;
		text.css('font-size', fontSize);
		counter=0;
		while (counter<100 && (text.width() > divWidth - 45)){
			counter+=1;
			text.css('font-size', fontSize -= 0.5);					
		}
		text.css('display', 'inline');
	}

	//CREATES A COOKIE
	function createCookie(name, value, days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			var expires = "; expires=" + date.toGMTString();
		} else var expires = "";
		document.cookie = name + "=" + value + expires + "; path=/";
	}

	//READS A COOKIE
	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			
			while (c.charAt(0) == ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}



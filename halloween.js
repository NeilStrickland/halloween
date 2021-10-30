var halloween = {};

halloween.woods = [
 [-1.520670483542123,  53.34766218287741],
 [-1.520498138476774,  53.34686529330398],
 [-1.518859320545364,  53.34647783510098],
 [-1.517744484740862,  53.34360406039328],
 [-1.517752886197002,  53.34228167407219],
 [-1.518116008241556,  53.34077766589555],
 [-1.518877972982369,  53.34070722547669],
 [-1.518909326112611,  53.34009471164371],
 [-1.518413538029803,  53.33988383709964],
 [-1.514757364736242,  53.34025904288594],
 [-1.513140765173637,  53.34058790804263],
 [-1.511223619541231,  53.34126992817640],
 [-1.509883918054774,  53.34225275712899],
 [-1.509462547343051,  53.34256983717089],
 [-1.509841106820806,  53.34442983383653],
 [-1.510080036100957,  53.34490743986352],
 [-1.511688516531236,  53.34581886542532],
 [-1.513580567351953,  53.34646236742562],
 [-1.515400720905071,  53.34697390997092],
 [-1.518396776382652,  53.34797956411364],
 [-1.520670483542123,  53.34766218287741]
];		    

halloween.token = 'pk.eyJ1IjoibmVpbHN0cmlja2xhbmQiLCJhIjoiY2t2Y3dsNG9kNG9yZTJwcXd4NTlxZmVkdiJ9.K-4AglQjb6DSsq2ezkREHw';

halloween.pos_opts = {
 enableHighAccuracy : 1, maximumAge: 10000, timeout : 10000
};

halloween.pnpoly= function( nvert, vertx, verty, testx, testy ) {
 var i, j, c = false;
 for( i = 0, j = nvert-1; i < nvert; j = i++ ) {
  if( ( ( verty[i] > testy ) != ( verty[j] > testy ) ) &&
      ( testx < ( vertx[j] - vertx[i] ) * ( testy - verty[i] ) / ( verty[j] - verty[i] ) + vertx[i] ) ) {
   c = !c;
  }
 }
 return c;
};

halloween.check_in_woods = function(x,y) {
 var w = this.woods;
 var n = w.length;
 var c = 0;
 var i,j;
 for (i = 0, j = n-1; i < n; j = i++ ) {
  if ((( w[i][1] > y ) != ( w[j][1] > y )) &&
      ( x < ( w[j][0] - w[i][0] ) * (y - w[i][1]) / ( w[j][1] - w[i][1] ) + w[i][0] ) ) {
   c = !c;
  }
 }
 return c;
};

halloween.play_noise = function() {
 var me = this;
 
 try {
  if (this.is_in_woods) {
   var i = Math.floor(this.noises.length * Math.random());
   if (this.noise.canPlayType('audio/mpeg')) {  
    this.noise.src = 'noise/' + noises[i] + '.mp3';
   } else if (this.noise.canPlayType('audio/aac')) {
    this.noise.src = 'noise/' + noises[i] + '.aac';
   }
   this.noise.autoplay = true;
   this.noise.load();
  }
 } catch(e) {
 }

 var t = Math.floor((1 + Math.random() * 3) * 60 * 1000);
 // var t = 10000;

 setTimeout(me.play_noise,t);
};

halloween.set_lat_lng = function(p) {
 var me = this;
 
 if (p) {
  var first = (this.lng == 0 && this.lat == 0);

  this.lng = p.coords.longitude;
  this.lat = p.coords.latitude;

  var was_in_woods = this.is_in_woods;
  this.is_in_woods = this.check_in_woods(this.lng,this.lat);

  if (first) {
   map.panTo(new google.maps.LatLng(lat,lng));
  }

  if (this.is_in_woods && ! was_in_woods) {
   try {
    this.noise.src = 'noise/danger.mp3';
    this.noise.autoplay = true;
    this.noise.load();
   } catch(e) {
   }
  }
 }

 setTimeout(function() {
  if (me.is_fake) {
   me.set_lat_lng({coords : {latitude : me.lat + 0.0001, longitude : me.lng + 0.0001}});
  } else {
   if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(me.set_lat_lng,me.try_again,me.pos_opts);
   }
  }
 },10000);
};

halloween.try_again = function() {
 var me = this;
 
 setTimeout(function() {
  if (me.is_fake) {
   me.set_lat_lng({coords : {latitude : me.lat + 0.0001, longitude : me.lng + 0.0001}});
  } else {
   if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(me.set_lat_lng,me.try_again,me.pos_opts);
   }
  }
 },10000);
};

halloween.dist = function(lat1,lng1,lat2,lng2) {
 var a1 = lat1 * Math.PI/180;
 var b1 = lng1 * Math.PI/180;
 var a2 = lat2 * Math.PI/180;
 var b2 = lng2 * Math.PI/180;

 var dlat = (lat2 - lat1) * Math.PI/180;
 var dlng = (lng2 - lng1) * Math.PI/180;
		
 var a  = Math.pow(Math.sin((a2-a1)/2),2) + Math.cos(a1) * Math.cos(a2) * Math.pow(Math.sin((b2-b1)/2),2);
 var c  = 2 * Math.atan2(Math.sqrt(a),Math.sqrt(1-a)); // great circle distance in radians
 var dk = c * 6373000; 

 return dk;
};

halloween.show = function(p) {
 this.pos = p;

 for (var i in this.pos) {
  x = parseFloat(pos[i][0]);
  y = parseFloat(pos[i][1]);
  j = parseInt(pos[i][2]);

  if(this.markers[i]) {
   this.markers[i].setPosition(new google.maps.LatLng(x,y));
  } else {
   var s;
   
   if (this.is_mobile) {
    s = (i == this.id || i == 666) ? 80 : 60;
   } else {
    s = (i == this.id || i == 666) ? 48 : 36;
   }

   var icon = new google.maps.MarkerImage('icons/' + j + '.png',null,null,null,new google.maps.Size(s,s));

   this.markers[i] = new google.maps.Marker({
    position : new google.maps.LatLng(x,y),
    map: map,
    icon : icon,
    title : i
   });
  }
 }

 if (this.markers[666]) {
  m = this.markers[666];
  xy = m.getPosition();
  x = xy.lat();
  y = xy.lng();
  d = dist(lat,lng,x,y);
  if (d < 100) {
   m.setVisible(false);
  } else {
   m.setVisible(true);
  }
 }
};

halloween.fetch = function() {
 var me = this;
 
 $.ajax({
  type : 'POST',
  url : 'index.php',
  data : {id : this.id, cmd : 'send_data', lat : this.lat, lng : this.lng},
  success : show
//  ,error : function(j,s,e) {alert('Ajax error: ' + s);}
 }).always(setTimeout(function() { me.fetch(); },10000));
};

halloween.reg_button_handler = function(i) {
 var f,j,e,t;
 
 f = document.reg_form;
 for (j in f.elements) {
  e = f.elements[j];
  if (e.name == "icon_id") {
   t = document.getElementById("image_td_" + e.value);
   if (e.value == i) {
    t.className = "selected";
   } else {
    t.className = "unselected";
   }
  }
 }
}

halloween.reg_image_handler = function(i) {
 var f,j,e,t;
 
 f = document.reg_form;
 for (j in f.elements) {
  e = f.elements[j];
  e.checked = (e.value == i);
  if (e.name == "icon_id") {
   t = document.getElementById("image_td_" + e.value);
   if (e.value == i) {
    t.className = "selected";
   } else {
    t.className = "unselected";
   }
  }
 }
}

halloween.init = function() {
 this.noise = document.getElementById('noise');
 this.markers = {};

 $("#start").css({"display" : "none"});
 $("#map").css({"width":$(document).width(),"height":$(document).height()});

 this.map = L.map('map').setView([53.343809,-1.514333],17);

 L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    maxZoom: 18,
    id: 'mapbox/satellite-v9',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: this.token
 }).addTo(this.map);
};

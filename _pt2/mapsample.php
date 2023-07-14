<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false">
</script>
 
<script type="text/javascript">
var myCenter=new google.maps.LatLng(39.46910009461806, -0.39186259999996764);
 
var marker = new google.maps.Marker({
        position: myCenter
        ,});
 
window.onload = function() {
        var mapOptions = {
          center: myCenter,
          zoom: 16,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          panControl: false,
          zoomControl: false,
          scaleControl: false,
          };
        var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    marker.setMap(map);
        };
</script>
<body>
<div id="map_canvas"></div>
</body>
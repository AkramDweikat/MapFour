$(function(){
  var mapOptions = {
    zoom: 4,
    center: new google.maps.LatLng(-25.363882, 131.044922)
  };

  var map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

  var marker = new google.maps.Marker({
    position: map.getCenter(),
    map: map,
    title: 'Click to zoom',

  });

  function moveMap(distance){
	var point = map.getCenter();
	var projection = map.getProjection();
	var pixelpoint = projection.fromLatLngToPoint(point);
	pixelpoint.x = pixelpoint.x - distance;

	var newpoint = projection.fromPointToLatLng(pixelpoint);

	 map.setCenter(newpoint);  	
  }

  google.maps.event.addListener(marker, 'click', function() {

	moveMap(20);    

	 $("#map-content").animate({
	 	right:"25%"
	 }, 500)

  });

  $("#map-content-close").on('click',function(){

	moveMap(-20);    

	 $("#map-content").animate({
	 	right:"100%"
	 }, 200)
  })

  $(".timeline-event").click(function(e){
    alert("Position marker!");
    e.stopPropagation();
  })

  $(".timeline-event").each(function(element){
    offset = $(this).data("offset");
    $(this).animate({ left: offset },50);
  })

});

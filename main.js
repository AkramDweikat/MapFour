$(function(){

  // initialize markers array
  var markers = [];

  // global variable use to hold the map center whenever the map
  // box is resized
  var current_center;

  // delete all current markers
  var setAllMap = function(map){
    for(var i = 0, l = markers.length; i<l; i++){
      markers[i].setMap(map);
      delete markers[i];
    }
  }

  // very ugly code to slide sidebar in and out
    var hideSideBar = function(){

    current_center = map.getCenter();

    $("#map-content").animate({ right:"100%" }, 500);

    $("#map-canvas").animate({
      width:"100%",
      marginLeft:"0"
    },{ 
      duration: 500, 
      complete: function(){
        google.maps.event.trigger(map, 'resize');
        map.setCenter(current_center);
      } 
    });

  }

  var showSideBar = function(){

    current_center = map.getCenter();

    $("#map-content").animate({ right:"25%" }, 500);

    $("#map-canvas").animate({
      width:"25%",
      marginLeft:"75%"
    },{ 
      duration: 500, 
      complete: function(){
        google.maps.event.trigger(map, 'resize');
        map.setCenter(current_center);
      } 
    });

  }

  // initialize mapp
  var map = new google.maps.Map($("#map-canvas")[0], {
    zoom: 4,
    center: new google.maps.LatLng(-25.363882, 131.044922),
    panControl: true,
    panControlOptions: {
      position: google.maps.ControlPosition.TOP_RIGHT
    },
    streetViewControl: false,
    zoomControlOptions: {
      style: google.maps.ZoomControlStyle.LARGE,
      position: google.maps.ControlPosition.TOP_RIGHT
    }
  });

  // click on the 'x' and slide sidebar out
  $("#map-content-close").on('click',function(){
	   hideSideBar();
  })

  // initialize ion slider plugin
  $("#date_range").ionRangeSlider({
      type: "double", // range
      min: +moment().subtract(1, "years").format("X"),
      max: +moment().format("X"),
      from: +moment().subtract(6, "months").format("X"),
      prettify: function (num) {
          return moment(num, "X").format("LL"); // date format
      },
      onFinish: function (data) {

          // when the range changes
          var from  = moment.unix(data.from);
          var to    = moment.unix(data.to); 
          
          // get the map boundaries
          var bounds = map.getBounds();
          var ne = bounds.getNorthEast(); // LatLng of the north-east corner
          var sw = bounds.getSouthWest(); // LatLng of the south-west corder
          var nw = new google.maps.LatLng(ne.lat(), sw.lng());
          var se = new google.maps.LatLng(sw.lat(), ne.lng());

          // delete all markers
          setAllMap(null);

          // start progress bar
          NProgress.start();

          // ajax get request
          $.get("https://dl.dropboxusercontent.com/u/6777363/test.json", function(data){

            // finish progress bar
            NProgress.done();

            // for each marker received
            $.each(data, function(key,marker){

              // create a new marker
              var marker = new google.maps.Marker({
                position: new google.maps.LatLng(marker.lat,marker.lon),
                map: map,
                title: 'Click to zoom',
              });

              // push reference into an array
              markers.push(marker);

              // add event listener
              // when you click on a marker show sidebar
              google.maps.event.addListener(marker, 'click', function() {
                showSideBar();
              });

              // if it's the last marker (all have been set)
              // center the map on the first marker
              if (key === data.length -1){
                map.setCenter(new google.maps.LatLng(data[0].lat,data[0].lon));
              }            
            })

          },'json')

      }
  });

});

$(function(){
  var markers = [];
  var current_center;

  var setAllMap = function(map){
    for(var i = 0, l = markers.length; i<l; i++){
      markers[i].setMap(map);
      delete markers[i];
    }
  }

  var mapOptions = {
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
  };

  var map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

  $("#map-content-close").on('click',function(){

	   hideSideBar();
  })

  var hideSideBar = function(){

    current_center = map.getCenter();

    $("#map-content").animate({
      right:"100%"
    }, 500);

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

    $("#map-content").animate({
      right:"25%"
    }, 500);

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

$("#date_range").ionRangeSlider({
    type: "double",
    min: +moment().subtract(1, "years").format("X"),
    max: +moment().format("X"),
    from: +moment().subtract(6, "months").format("X"),
    prettify: function (num) {
        return moment(num, "X").format("LL");
    },
    onFinish: function (data) {
        var from  = moment.unix(data.from);
        var to    = moment.unix(data.to); 
        
        var bounds = map.getBounds();
        var ne = bounds.getNorthEast(); // LatLng of the north-east corner
        var sw = bounds.getSouthWest(); // LatLng of the south-west corder
        var nw = new google.maps.LatLng(ne.lat(), sw.lng());
        var se = new google.maps.LatLng(sw.lat(), ne.lng());

        setAllMap(null);
        $.get("https://dl.dropboxusercontent.com/u/6777363/test.json", function(data){

          $.each(data, function(key,marker){

            var marker = new google.maps.Marker({
              position: new google.maps.LatLng(marker.lat,marker.lon),
              map: map,
              title: 'Click to zoom',

            });

            markers.push(marker);

            google.maps.event.addListener(marker, 'click', function() {

              showSideBar();

            });

            if (key === data.length -1){
              map.setCenter(new google.maps.LatLng(data[0].lat,data[0].lon));
            }            
          })

        },'json')

    }
});

});

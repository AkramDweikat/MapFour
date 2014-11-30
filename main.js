$(function(){

  // initialize markers object
  var markers = {};
  var articles = {};

  // global variable use to hold the map center whenever the map
  // box is resized
  var current_center;

  // delete all current markers
  var deleteMarkers = function(map){
    $.each(markers, function(key){
      markers[key].setMap(map);
      delete markers[key];      
    })
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
    center: new google.maps.LatLng(25.363882, 30.044922),
    panControl: true,
    mapTypeControl: false,
    zoomControlOptions: {
      style: google.maps.ZoomControlStyle.SMALL,
      position: google.maps.ControlPosition.TOP_RIGHT
    },
    panControlOptions: {
      position: google.maps.ControlPosition.TOP_RIGHT
    },
    streetViewControl: false,
  });

 var input = ($("<input type='text' id='map_search' />")[0]); 
 map.controls[google.maps.ControlPosition.BOTTOM_RIGHT].push(input);


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
          
          // get the map boundaries
          var bounds = map.getBounds();
          var ne = bounds.getNorthEast(); // LatLng of the north-east corner
          var sw = bounds.getSouthWest(); // LatLng of the south-west corder
          var nw = new google.maps.LatLng(ne.lat(), sw.lng());
          var se = new google.maps.LatLng(sw.lat(), ne.lng());

          // delete all markers
          deleteMarkers(null);

          // start progress bar
          NProgress.start();

          // ajax get request
          $.get("http://10.52.64.222/index.php/Articles?start="+data.from+"&end="+data.to+"&tl="+nw.lat()+","+nw.lng()+"&br="+se.lat()+","+se.lng(), 
            function(data){

            // finish progress bar
            NProgress.done();

            // for each marker received
            $.each(data['articles'], function(key,article){

              // create a new marker
              var marker = new google.maps.Marker({
                position: new google.maps.LatLng(article.latitude,article.longitude),
                map: map,
                animation: google.maps.Animation.DROP,
                title: 'Click to zoom',
                id_article: article.id
              });

              var objectKey = article.latitude+article.longitude;

              console.log(objectKey);

              if(articles[article.id] !== undefined){
                articles[article.id].push(article);
                markers[article.id].push(marker);
              } else {                
                articles[article.id] = [article];
                markers[article.id]= [marker];
              }
     
              // add event listener
              // when you click on a marker show sidebar
              google.maps.event.addListener(marker, 'click', function() {
                
                showSideBar();

                console.log(articles[this.id_article].title);
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

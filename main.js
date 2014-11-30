$(function(){

  // initialize markers object
  var markers = {};
  var articles = {};
  var data_from, data_to;
  var global_selected;
  var indexes = [];
  var currentIndex = 0;

  // global variable use to hold the map center whenever the map
  // box is resized
  var current_center;

  var loadMarkers  = function (url,callback) {
          deleteMarkers();

          // start progress bar
          NProgress.start();

          // ajax get request
          $.get(url, function(data){

            var current = window.location.hash.split("#")[1];
            // finish progress bar
            NProgress.done();

            // for each marker received
            $.each(data['articles'], function(key,article){
              var markerIcon;
              
//              if(article.video !== undefined){
//                markerIcon = 'https://cdn0.iconfinder.com/data/icons/web-8/106/Television-64.png';
//              } else {
                markerIcon = 'https://cdn1.iconfinder.com/data/icons/google_jfk_icons_by_carlosjj/64/news.png';
//              }

              // create a new marker
              var marker = new google.maps.Marker({
                position: new google.maps.LatLng(article.latitude,article.longitude),
                map: map,
                icon:markerIcon,
                animation: google.maps.Animation.DROP,
                title: 'Click to zoom',
              });

              var objectKey = String(marker.position.lat())+String(marker.position.lng());


              if(current !== undefined && current == objectKey){
                marker.setIcon('https://cdn2.iconfinder.com/data/icons/snipicons/500/map-marker-64.png');
                map.setCenter(marker.position);
                currentIndex = indexes.length;
              }

              // push reference into an array
              if(markers[objectKey] !== undefined){
                articles[objectKey].push(article);
                markers[objectKey].push(marker);
              } else {
                articles[objectKey] = [article];
                markers[objectKey] = [marker];                
              }

              indexes.push(objectKey);

              // add event listener
              // when you click on a marker show sidebar
              google.maps.event.addListener(marker, 'click', function() {
                
                var articleList = articles[String(marker.position.lat())+String(marker.position.lng())];

                // more than one article on that marker
                if(articleList.length > 1) {
                  loadArticleList(articleList);
                } else {
                  window.location.hash = String(marker.position.lat())+String(marker.position.lng());
                  loadArticle(articleList[0], marker);
                }
              });

              if(callback !== undefined){                  
                callback.call();
              }

            })

          },'json')

  }

  // delete all current markers
  var deleteMarkers = function(){
    $.each(markers, function(key,m){
      $.each(m, function(k){
        m[k].setMap(null);        
        delete m[k];      
      });

      delete markers[key];

    })

    markers = {};
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

  var slideMap = function(){
    $("#map-wrapper").animate({height:200 },200);
    $("#date_range_wrapper").slideUp(200);
    $("#date_range_trigger").show();

    google.maps.event.trigger(map, 'resize');    
  }

  $("#date_range_trigger").on('click',function(){
    $("#map-wrapper").animate({height:400 },200);
    $("#date_range_wrapper").slideDown(200);
    $(this).hide();
  });

  var ul = $("#article_list");

  ul.delegate('a','click',function(e){
    e.preventDefault();

    window.location.hash = $(this).data('latlng');
                  
    loadArticle(articles[$(this).data('latlng')][0]);
  });

  var loadArticleList = function(list){
    showSideBar();

    ul.html("");

    $.each(list, function(key,article){
      var li = $("<li />");

      var marker = new google.maps.Marker({
        position: new google.maps.LatLng(article.latitude,article.longitude),
        map: null,
        animation: google.maps.Animation.DROP,
        title: 'Click to zoom',
      });

      var objectKey = String(marker.position.lat())+String(marker.position.lng());

      li.append(
        $("<a />").attr("href",article.link).data('latlng',objectKey).text(article.title)
      );

      ul.append(li);      
    })
  }

  var mainArticleH1 = $("#main_article_title");
  var mainArticleBody = $("#main_article_body");;

  var loadArticle = function(article) {
    $(".content-arrows").show();
    mainArticleH1.text(article.title);
    mainArticleBody.html(article.body);

    hideSideBar();

    loadMarkers("http://10.52.64.222/index.php/Articles/related?story_id="+article.id);

    slideMap();
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
    styles:[
    {
        "featureType": "road",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "transit",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "administrative.province",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "water",
        "stylers": [
            {
                "color": "#004b76"
            }
        ]
    },
    {
        "featureType": "landscape.natural",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#fff6cb"
            }
        ]
    },
    {
        "featureType": "administrative.country",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#7f7d7a"
            },
            {
                "lightness": 10
            },
            {
                "weight": 1
            }
        ]
    }
]
  });

 var input = $("<input type='text' id='map_search' />"); 

 input.on('keyup', function(e){
  if(e.keyCode == 13){}
 });

 map.controls[google.maps.ControlPosition.BOTTOM_RIGHT].push(input[0]);


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
          
          data_from = data.from;
          data_to = data.to;

          // get the map boundaries
          var bounds = map.getBounds();
          var ne = bounds.getNorthEast(); // LatLng of the north-east corner
          var sw = bounds.getSouthWest(); // LatLng of the south-west corder
          var nw = new google.maps.LatLng(ne.lat(), sw.lng());
          var se = new google.maps.LatLng(sw.lat(), ne.lng());

          loadMarkers("http://10.52.64.222/index.php/Articles?start="+data.from+"&end="+data.to+"&tl="+nw.lat()+","+nw.lng()+"&br="+se.lat()+","+se.lng());

      }
  });

});

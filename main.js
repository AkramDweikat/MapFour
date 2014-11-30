$(function(){

  // initialize markers object
  var markers = {};
  var articles = {};
  var data_from, data_to;

  // global variable use to hold the map center whenever the map
  // box is resized
  var current_center;

  var loadMarkers  = function (url) {
          deleteMarkers();

          // start progress bar
          NProgress.start();

          // ajax get request
          $.get(url, function(data){

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
              });

              var objectKey = String(marker.position.lat())+String(marker.position.lng());

              // push reference into an array
              if(markers[objectKey] !== undefined){
                articles[objectKey].push(article);
                markers[objectKey].push(marker);
              } else {
                articles[objectKey] = [article];
                markers[objectKey] = [marker];                
              }

              // add event listener
              // when you click on a marker show sidebar
              google.maps.event.addListener(marker, 'click', function() {
                
                var articleList = articles[String(marker.position.lat())+String(marker.position.lng())];

                // more than one article on that marker
                if(articleList.length > 1) {
                  loadArticleList(articleList);
                } else {
                  loadArticle(articleList[0]);
                }
              });

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

  var ul = $("#article_list");

  ul.delegate('a','click',function(e){
    e.preventDefault();
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
    mainArticleH1.text(article.title);
    mainArticleBody.html(article.body);

    hideSideBar();

    loadMarkers("http://10.52.64.222/index.php/Articles/related?story_id="+article.id);

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

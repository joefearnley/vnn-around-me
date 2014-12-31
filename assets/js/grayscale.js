$(window).scroll(function() {
    if ($('.navbar').offset().top > 50) {
        $('.navbar-fixed-top').addClass('top-nav-collapse');
    } else {
        $('.navbar-fixed-top').removeClass('top-nav-collapse');
    }
});

$(function() {
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var ref = new Firebase('https://vnn-around-me.firebaseio.com');
			var geoFire = new GeoFire(ref);

			ref.on('value', function(snapshot) {
                var schools = snapshot.val().data;
                var length = schools.length;
                var closestSchool = null;
                var shortestDistance = 0;
                for (var i = 0; i < length; i++) {
                    var userLocation = [position.coords.latitude, position.coords.longitude];
                    var schoolLocation = [parseFloat(schools[i].latitude), parseFloat(schools[i].longitude)];

                    distance = GeoFire.distance(userLocation, schoolLocation);
                    
                    if(i === 0) {
                        shortestDistance = distance;
                        closestSchool = schools[i];
                    } else if(distance < shortestDistance) {
                        shortestDistance = distance;
                        closestSchool = schools[i];
                    }
                }

                loadMap(position, closestSchool);
            }, function (errorObject) {
                console.log("The read failed: " + errorObject.code);
                $('#map').html(errorObject.code);
            });

        }, function() {
            $('#map').html('Geolocation service failed.');
        });
    } else {
        $('#map').html('Browser does not support Geolocation');
    }
});

function loadMap(position, school) {
    console.log('loading map');
    var mapOptions = {
        zoom: 13,
        center: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
        disableDefaultUI: true,
        scrollwheel: false,
        draggable: true,
    };

    var mapElement = document.getElementById('map');
    var map = new google.maps.Map(mapElement, mapOptions);
    var origin = position.coords.latitude + ',' + position.coords.longitude;
    var destination = school.latitude + ',' + school.longitude;
    var service = new google.maps.DirectionsService();

    var request = {
        origin: origin,
        destination: destination,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
    };

    service.route(request, function(response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            new google.maps.DirectionsRenderer({
                map: map,
                directions: response,
                suppressMarkers: true
            });

            var route = response.routes[0].legs[0];
            var startMarker = new google.maps.Marker({
                position: route.start_location,
                map: map,
                animation: google.maps.Animation.DROP,
                title: 'Start Point'
            });

            var startingMarkerContent = '<div class="marker-info">You are Here</div>';
            var startInfoWindow = new google.maps.InfoWindow();
            startInfoWindow.setContent(startingMarkerContent);
            startInfoWindow.open(map, startMarker); 

            google.maps.event.addListener(startMarker, 'click', function() {
                startInfoWindow.open(map, startMarker);
            });

            var endMarker = new google.maps.Marker({
                position: route.end_location,
                map: map,
                animation: google.maps.Animation.DROP,
                title: 'End Point'
            });

            var endingMarkerContent = '<div class="marker-info">'+
                    '<h4>'+school.name+'</h4>'+
                    '<div>'+school.address+'<br>'+
                    school.city+' '+school.state+' '+school.zip;

            if(school.url !== undefined) {
                endingMarkerContent += '<br><a href="'+school.url+'" target="_blank">'+school.url+'</a>';
            }

            endingMarkerContent += '</div></div>';
            var endInfowindow = new google.maps.InfoWindow();
            endInfowindow.setContent(endingMarkerContent);
            endInfowindow.open(map, endMarker); 

            google.maps.event.addListener(endMarker, 'click', function() {
                endInfoWindow.open(map, endMarker);
            });
        } else {
            console.log("Unable to retrieve your route");
        }
    });
}


$(window).scroll(function() {
    if ($(".navbar").offset().top > 50) {
        $(".navbar-fixed-top").addClass("top-nav-collapse");
    } else {
        $(".navbar-fixed-top").removeClass("top-nav-collapse");
    }
});

$(function() {
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            console.log('latitude : ' + position.coords.latitude);
            console.log('longitude : ' + position.coords.longitude);

            var jqxhr = $.get('/school/find', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            });
            
            jqxhr.done(function(response) {
                console.log('success');

                // load the map
                loadMap(position, response);

                // update header with closest school and information about it. 


            });
            
            jqxhr.fail(function(response) {
                console.log(response.responseText);
            });

        }, function() {
            console.log('Geolocation service failed.');
        });
    } else {
        console.log('Browser does not support Geolocation');
    }
});

function loadMap(position, school) {
    var mapOptions = {
        zoom: 13,
        center: new google.maps.LatLng(position.coords.latitude, position.coords.longitude),
        disableDefaultUI: true,
        scrollwheel: false,
        draggable: true,
        //styles: [{"featureType":"water","elementType":"geometry","stylers":[{"color":"#a2daf2"}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f7f1df"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#d0e3b4"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#bde6ab"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","elementType":"geometry","stylers":[{"color":"#fbd3da"}]},{"featureType":"poi.business","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffe15f"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#efd151"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"black"}]},{"featureType":"transit.station.airport","elementType":"geometry.fill","stylers":[{"color":"#cfb2db"}]}]
    };

    var mapElement = document.getElementById('map');
    var map = new google.maps.Map(mapElement, mapOptions);

    // Custom Map Marker Icon - Customize the map-marker.png file to customize your icon
    // var myLatLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    // var beachMarker = new google.maps.Marker({
    //     position: myLatLng,
    //     map: map,
    // });

    var origin = position.coords.latitude + ',' + position.coords.longitude;
    var destination = school.latitude + ',' + school.longitude;

    var service = new google.maps.DirectionsService();
    var request = {
        origin: origin,
        destination: destination,
        travelMode: google.maps.DirectionsTravelMode.DRIVING
    };

    service.route(
        request,
        function(response, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                // new google.maps.DirectionsRenderer({
                //     map: map,
                //     directions: response
                // });
                var route = response.routes[0].legs[0];

                for (var i = 0; i < route.steps.length; i++) {
                    var marker = new google.maps.Marker({
                        position: route.steps[i].start_location,
                        map: map
                    });
                    
                    //attachInstructionText(marker, myRoute.steps[i].instructions);
                    // google.maps.event.addListener(marker, 'click', function() {
                    // });

                    var beachMarker = new google.maps.Marker({
                        position: myLatLng,
                        map: map,
                    });
                    var inforWindow = new google.maps.InfoWindow();
                    inforWindow.setContent('Blah');
                    inforWindow.open(map, marker);
                    //markerArray[i] = marker;
                }

            } else {
                console.log("Unable to retrieve your route");
            }
        });
}

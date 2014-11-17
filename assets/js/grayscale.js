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
            var jqxhr = $.get('/school/find', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            });

            jqxhr.done(function(response) {
                console.log('success');
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

            var startInfowindow = new google.maps.InfoWindow({
            });

            var route = response.routes[0].legs[0];
            var startMarker = new google.maps.Marker({
                position: route.start_location,
                map: map,
                animation: google.maps.Animation.DROP
            });
            startInfowindow.setContent('lsakdfalks');
            startInfowindow.open(map, startMarker); 

            var endMarker = new google.maps.Marker({
                position: route.end_location,
                map: map,
                animation: google.maps.Animation.DROP
            });
             var endInfowindow = new google.maps.InfoWindow({
            });
            endInfowindow.setContent('lsakdfalks');
            endInfowindow.open(map, endMarker); 

            // TODO: add click events to each marker/InfoWindow


        } else {
            console.log("Unable to retrieve your route");
        }
    });
}
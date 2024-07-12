@extends('dashboard.layout.app')
@section('title', 'Users Map')
@section('content')
<style>
    #map {
      height: 750px;
      width: 100%;
    }
  </style>
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div id="map"></div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
<script>
    function initMap() {
      // Create a map instance
      const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 24.130770, lng: 54.162845 },
        zoom: 8
      });
      let array_users=[];
      $.ajax({
                url: '/get_all_users', // Replace with the actual endpoint URL
                type: 'GET',
                 // Replace with the actual search term or data
                 success: function(response) {
  array_users = response.users.map(user => ({
    name: user.name,
    email: user.email,
    lat: user.lat,
    lng: user.lng,
    section: user.section.name,
    group: user.group,
    role: user.guard
  }));

  // Create an array to store the markers
  const markers = [];

  array_users.forEach(user => {
    if (user.lat != null && user.lng != null) {
      const marker = new google.maps.Marker({
        position: { lat: user.lat, lng: user.lng },
        title: user.name
      });

      // Create info window for the marker
      const infoWindow = new google.maps.InfoWindow({
        content: `<strong style="color:red;"><b>${user.name}</b></strong><br><spam style="color:blue;">${user.email}</spam><br>Role: ${user.role}<br>Section: ${user.section}<br>Group: ${
          user.group ? user.group.name : ''
        }<br>Lat: ${user.lat}<br>Lng: ${user.lng}`
      });

      // Show info window when marker is clicked
      marker.addListener('click', () => {
        infoWindow.open(map, marker);
      });

      markers.push(marker);
    }
  });

  // Create a MarkerClusterer instance to cluster the markers
  const markerCluster = new MarkerClusterer(map, markers, {
    imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
    gridSize: 50,
    maxZoom: 15
  });
},
                error: function(xhr, status, error) {
                    // Handle any errors that occur during the AJAX request
                    console.log(xhr.responseText);
                }
            });
      // User data with lat, lng, name, and email
    //   const users = [
    //     { lat: 24.130770, lng: 54.162845, name: 'User 1', email: 'user1@example.com' },
    //     { lat: 51.5074, lng: -0.1278, name: 'User 2', email: 'user2@example.com' },
    //     // Add more user data here...
    //   ];

      // Create markers for each user
      
    }
  </script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA8j52K1H0busaXOPb_4H9NUHkZqBlLae8&callback=initMap" async defer></script>
@endpush
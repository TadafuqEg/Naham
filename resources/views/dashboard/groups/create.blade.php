@extends('dashboard.layout.app')
@section('title', 'Create Group')
@section('content')
<style>
    #map {
        height: 800px;
        width: 100%;
    }
</style>
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-secondary rounded h-100 p-4">
                <h6 class="mb-4">Create Group</h6>
                <form action="{{ route('create.group') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputEmail3"  name="name" value="{{ old('name') }}">
                            @if ($errors->has('name'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('name') }}</p>
                        @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-sm-2 col-form-label">Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputEmail3"  name="code" value="{{ old('code') }}">
                            @if ($errors->has('code'))
                            <p class="text-error more-info-err" style="color: red;">
                                {{ $errors->first('code') }}</p>
                        @endif
                        </div>
                    </div>
                    
                    @if(auth()->check() && auth()->user()->hasRole('super super admin'))
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Section</label>
                            <div class="col-sm-10">
                                <select class="form-select" id="floatingSelect" name="section"
                                    aria-label="Floating label select example">
                                    <option value="" selected disabled>Select Section</option>
                                    @foreach($sections as $section)
                                    <option value="{{$section->id}}" @if(old('section')==$section->id) selected @endif>{{ucwords($section->name)}}</option>
                                    @endforeach
                                
                                </select>
                                @if ($errors->has('section'))
                                <p class="text-error more-info-err" style="color: red;">
                                    {{ $errors->first('section') }}</p>
                            @endif
                            </div>
                        </div>
                    @endif
                    <div class="row mb-3">
                        <legend class="col-form-label col-sm-2 pt-0">Available Chat</legend>
                        <div class="col-sm-10">
                            
                                <div class="form-check form-switch">
                                    <input class="form-check-input" name="active_chat"  type="checkbox" role="switch"
                                        id="flexSwitchCheckDefault1" value="1" @if(old('active_chat')) 
                                        checked 
                                    @endif >
                                    <label class="form-check-label" for="flexSwitchCheckDefault1">available chat</label>
                                </div>
                               
                            
                            
                        </div>
                    </div>
                    <div class="row mb-3">
                        <legend class="col-form-label col-sm-2 pt-0">Activation</legend>
                        <div class="col-sm-10">
                            
                                <div class="form-check form-switch">
                                    <input class="form-check-input" name="is_active"  type="checkbox" role="switch"
                                        id="flexSwitchCheckDefault" value="1" @if(old('is_active')) 
                                        checked 
                                    @endif >
                                    <label class="form-check-label" for="flexSwitchCheckDefault">active</label>
                                </div>
                               
                            
                            
                        </div>
                    </div>
                    <div class="row mb-3">
                        <legend class="col-form-label col-sm-2 pt-0">Specify Area on Map</legend>
                        <div class="col-sm-10">
                            <div id="map">

                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="coordinatesInput" name="coordinates">
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA8j52K1H0busaXOPb_4H9NUHkZqBlLae8&libraries=drawing"></script>
<script>
    var map;
var drawingManager;
var coordinates = [];
var polygons = [];
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 24.130770, lng: 54.162845 },
        zoom: 8
    });

    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [google.maps.drawing.OverlayType.POLYGON]
        }
    });

    drawingManager.setMap(map);

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function (event) {
        
        if (event.type == google.maps.drawing.OverlayType.POLYGON) {
            var polygon = event.overlay;
            polygons.push(polygon);
            var path = polygon.getPath().getArray();
            coordinates = path.map(function (point) {
                return { lat: point.lat(), lng: point.lng() };
            });

            // Send the coordinates via AJAX
            //sendCoordinates(coordinates);
            document.getElementById('coordinatesInput').value = JSON.stringify(coordinates);
            removePreviousPolygons(polygon);
            // Remove the polygon from the map
            //polygon.setMap(null);
        }
        
    });
    
}
function removePreviousPolygons(currentPolygon) {
    
  polygons.forEach(function (polygon) {
    if (polygon !== currentPolygon) {
      polygon.setMap(null);
    }
  });

  // Clear the polygons array except for the current polygon
  polygons = [currentPolygon];
}
// function sendCoordinates(coordinates) {
//     console.log(coordinates);
//     $.ajax({
//         url: '/your-endpoint', // Replace with the actual endpoint URL
//         type: 'POST',
//         data: JSON.stringify(coordinates),
//         contentType: 'application/json',
//         success: function (response) {
//             // Handle the success response here
//             //console.log(response);
//         },
//         error: function (xhr, status, error) {
//             // Handle any errors that occur during the AJAX request
//             //console.log(xhr.responseText);
//         }
//     });
// }

// Initialize the map
google.maps.event.addDomListener(window, 'load', initMap);
</script>
@endpush
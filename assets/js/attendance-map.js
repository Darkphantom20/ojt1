// attendance-map.js
let attendanceMap = null;
let attendanceMapMarker = null;
let attendanceGeocoder = null;

function initAttendanceMap() {
  if (!attendanceMap) {
    const defaultCenter = { lat: 12.8797, lng: 121.7740 };
    attendanceMap = new google.maps.Map(document.getElementById('attendanceMap'), {
      center: defaultCenter,
      zoom: 6,
      mapTypeId: 'hybrid',
      disableDefaultUI: false,
      zoomControl: true,
      mapTypeControl: true,
      streetViewControl: false,
      fullscreenControl: true,
    });

    attendanceMapMarker = new google.maps.Marker({
      map: attendanceMap,
      title: 'Location',
    });
    attendanceGeocoder = new google.maps.Geocoder();
  }
}

function updateAttendanceMapLocation(lat, lng) {
  initAttendanceMap();
  const pos = { lat: lat, lng: lng };
  attendanceMap.setCenter(pos);
  attendanceMap.setZoom(15);
  if (attendanceMapMarker) {
    attendanceMapMarker.setPosition(pos);
    attendanceMapMarker.setTitle('Current location: ' + lat.toFixed(6) + ', ' + lng.toFixed(6));

    if (attendanceGeocoder) {
      attendanceGeocoder.geocode({ location: pos }, function(results, status) {
        if (status === 'OK' && results[0]) {
          attendanceMapMarker.setTitle(results[0].formatted_address);
          const infowindow = new google.maps.InfoWindow({
            content: results[0].formatted_address,
          });
          infowindow.open(attendanceMap, attendanceMapMarker);
        }
      });
    }
  }
}

function refreshAttendanceGeolocation() {
  if (!navigator.geolocation) {
    const el = document.getElementById('attendanceLocationStatus');
    if (el) el.value = 'Geolocation not supported';
    return;
  }
  navigator.geolocation.getCurrentPosition(
    function (position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const el = document.getElementById('attendanceLocationStatus');
      if (el) {
        el.value = 'Current location: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
        el.classList.add('text-success');
      }
      updateAttendanceMapLocation(lat, lng);
    },
    function (err) {
      const el = document.getElementById('attendanceLocationStatus');
      if (el) {
        el.value = 'Geolocation error: ' + err.message;
        el.classList.add('text-danger');
      }
    },
    { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 }
  );
}

document.addEventListener('DOMContentLoaded', function() {
  initAttendanceMap();
  refreshAttendanceGeolocation();
});

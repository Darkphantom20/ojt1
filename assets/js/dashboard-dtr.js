let qrScanInterval;
let qrStream;

function updateClock() {
  var now = new Date();
  var time = now.toLocaleTimeString();
  $('#currentTime').text(time);
  $('#currentTimeLabel').text('Current time: ' + time);
  $('#bigCurrentTime').text(time);
  $('#bigCurrentTimeLabel').text('Current local time: ' + time);

  var seconds = now.getSeconds();
  var rotation = seconds * 6;
  $('#bigWatchIcon').css('transform', 'rotate(' + rotation + 'deg)');
}

let gmap = null;
let mapMarker = null;
let geocoder = null;
let accuracyCircle = null;

function showMapFallback(message) {
  const mapEl = document.getElementById('gmap');
  const fallbackEl = document.getElementById('gmapFallback');
  if (mapEl) {
    mapEl.style.display = 'none';
  }
  if (fallbackEl) {
    fallbackEl.style.display = 'block';
    fallbackEl.textContent = message || 'Google Maps is unavailable right now. Location will still be recorded, but the map is disabled.';
  }
}

function hideMapFallback() {
  const mapEl = document.getElementById('gmap');
  const fallbackEl = document.getElementById('gmapFallback');
  if (mapEl) {
    mapEl.style.display = 'block';
  }
  if (fallbackEl) {
    fallbackEl.style.display = 'none';
  }
}

window.gm_authFailure = function() {
  showMapFallback('Google Maps authentication failed. The map cannot be displayed.');
};

function initMap() {
  if (!gmap) {
    if (typeof google === 'undefined' || !google.maps) {
      showMapFallback('Google Maps could not load. Please check network or API settings.');
      return;
    }

    if (window._gmAuthFailed) {
      showMapFallback('Google Maps authentication failed. The map cannot be displayed.');
      return;
    }

    const defaultCenter = { lat: 12.8797, lng: 121.7740 }; // Philippines
    try {
      gmap = new google.maps.Map(document.getElementById('gmap'), {
        center: defaultCenter,
        zoom: 6,
        mapTypeId: 'hybrid',
        disableDefaultUI: false,
        zoomControl: true,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        gestureHandling: 'greedy',
      });
    } catch (err) {
      console.error('Google Maps initialization failed:', err);
      showMapFallback('Google Maps initialization failed. Location is still tracked without the map.');
      return;
    }

    hideMapFallback();
    
    // Create a more realistic and user-friendly marker with pulsing effect
    const markerIcon = {
      path: 'M12,0C7.58,0 4,3.58 4,8c0,5.25 8,13 8,13s8-7.75 8-13C20,3.58 16.42,0 12,0z M12,11.5c-1.93,0-3.5-1.57-3.5-3.5S10.07,4.5 12,4.5s3.5,1.57 3.5,3.5S13.93,11.5 12,11.5z',
      fillColor: '#4285F4',
      fillOpacity: 1,
      strokeColor: '#ffffff',
      strokeWeight: 2,
      anchor: new google.maps.Point(12, 24),
      scale: 1.5,
    };
    
    mapMarker = new google.maps.Marker({
      position: defaultCenter,
      map: gmap,
      title: 'Your location',
      icon: markerIcon,
      animation: google.maps.Animation.DROP,
      zIndex: 999,
    });
    
    // Add accuracy circle effect for current location
    accuracyCircle = new google.maps.Circle({
      strokeColor: '#4285F4',
      strokeOpacity: 0.3,
      strokeWeight: 1,
      fillColor: '#4285F4',
      fillOpacity: 0.1,
      map: gmap,
      center: defaultCenter,
      radius: 100,
      zIndex: 998,
    });
    
    geocoder = new google.maps.Geocoder();
  }
}

function setLocationStatus(text, type) {
  const el = $('#locationStatus');
  if (type === 'text-success') {
    $('#scanHint').text('Location acquired, QR ready.');
  }
  if (el.is('input')) {
    el.val(text);
  } else {
    el.text(text);
  }
  el.removeClass('text-muted text-success text-danger');
  el.addClass(type || 'text-muted');
}

function safeSetStartScan(isStarted) {
  // Chrome Mobile can keep buttons active while scanning; disable while scan active.
  $('.btn-login, .btn-logout').prop('disabled', isStarted);
  if (isStarted) {
    $('#scanHint').text('Scanning... please hold steady.');
  }
}

function safeSetStopScan(isStopped) {
  // Re-enable the buttons after scanning
  if (isStopped) {
    $('.btn-login, .btn-logout').prop('disabled', false);
    $('#scanHint').text('Scan stopped. Tap LOG IN/LOG OUT to restart.');
  }
}

function updateMapLocation(lat, lng, accuracy) {
  initMap();
  if (!gmap || typeof google === 'undefined' || !google.maps) {
    showMapFallback('Google Maps is unavailable. Location coordinates are still tracked.');
    return;
  }
  const pos = { lat: lat, lng: lng };
  gmap.setCenter(pos);
  gmap.setZoom(17);
  
  // Calculate radius based on accuracy (in meters)
  const radius = accuracy ? Math.max(50, accuracy * 3) : 100;
  
  if (mapMarker) {
    mapMarker.setPosition(pos);
    mapMarker.setTitle('Your location: ' + lat.toFixed(6) + ', ' + lng.toFixed(6));
    mapMarker.setAnimation(google.maps.Animation.DROP);
    
    // Update accuracy circle
    if (accuracyCircle) {
      accuracyCircle.setCenter(pos);
      accuracyCircle.setRadius(radius);
    }
    
    // Reverse geocode to get address
    if (geocoder) {
      geocoder.geocode({ location: pos }, function(results, status) {
        if (status === 'OK' && results[0]) {
          const address = results[0].formatted_address;
          mapMarker.setTitle(address);
          const infowindow = new google.maps.InfoWindow({
            content: '<div style="padding:8px; min-width:200px;"><div style="margin-bottom:5px;"><i class="fas fa-map-marker-alt text-primary"></i> <strong>Your Location</strong></div><div style="color:#666; font-size:12px;">' + address + '</div><div style="margin-top:5px; padding-top:5px; border-top:1px solid #eee; font-size:11px; color:#999;">Accuracy: ±' + Math.round(accuracy) + 'm</div></div>',
          });
          setTimeout(() => infowindow.open(gmap, mapMarker), 500);
        }
      });
    }
  }
}

function safeUpdateMapLocation(lat, lng, accuracy) {
  if (typeof google === 'undefined' || !google.maps || !gmap) {
    showMapFallback('Google Maps is unavailable. Location coordinates are still tracked.');
    return;
  }
  updateMapLocation(lat, lng, accuracy);
}

function refreshGeolocation() {
  if (!navigator.geolocation) {
    setLocationStatus('Geolocation not supported', 'text-danger');
    return;
  }
  setLocationStatus('Acquiring location...', 'text-muted');
  navigator.geolocation.getCurrentPosition(
    function (position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const accuracy = position.coords.accuracy;
      
      $('#geo_lat').val(lat);
      $('#geo_lng').val(lng);
      $('#form_geo_lat').val(lat);
      $('#form_geo_lng').val(lng);
      
      let accuracyText = accuracy <= 10 ? 'High' : accuracy <= 50 ? 'Medium' : 'Low';
      setLocationStatus('📍 ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + ' (' + accuracyText + ' accuracy)', 'text-success');
      updateMapLocation(lat, lng, accuracy);
    },
    function (err) {
      let errorMsg = err.message;
      if (err.code === 1) {
        errorMsg = 'Location permission denied. Please enable location access.';
      } else if (err.code === 2) {
        errorMsg = 'Position unavailable. Try going near a window.';
      } else if (err.code === 3) {
        errorMsg = 'Location request timed out. Try again.';
      }
      setLocationStatus('⚠️ ' + errorMsg, 'text-danger');
    },
    { 
      enableHighAccuracy: true, 
      timeout: 30000, 
      maximumAge: 0,
      maximumAge: 60000 
    }
  );
}

function startQRScan() {
  const video = document.getElementById('qrVideo');
  const canvas = document.getElementById('qrCanvas');
  const context = canvas.getContext('2d');

  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    alert('Camera not available in this browser.');
    return;
  }

  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
    .then(function (stream) {
      qrStream = stream;
      video.srcObject = stream;
      video.setAttribute('playsinline', true);
      video.play();

      safeSetStartScan(true);
      safeSetStopScan(false);

      function cloneImageData(src) {
    const dst = context.createImageData(src.width, src.height);
    dst.data.set(src.data);
    return dst;
  }

  function adjustGamma(srcData, gamma) {
    const dst = cloneImageData(srcData);
    const invGamma = 1 / gamma;
    for (let i = 0; i < dst.data.length; i += 4) {
      dst.data[i] = 255 * Math.pow(dst.data[i] / 255, invGamma);
      dst.data[i + 1] = 255 * Math.pow(dst.data[i + 1] / 255, invGamma);
      dst.data[i + 2] = 255 * Math.pow(dst.data[i + 2] / 255, invGamma);
    }
    return dst;
  }

  function thresholdImage(srcData, thresh) {
    const dst = cloneImageData(srcData);
    for (let i = 0; i < dst.data.length; i += 4) {
      const gray = Math.round(0.299 * dst.data[i] + 0.587 * dst.data[i + 1] + 0.114 * dst.data[i + 2]);
      const val = gray >= thresh ? 255 : 0;
      dst.data[i] = dst.data[i + 1] = dst.data[i + 2] = val;
    }
    return dst;
  }

  function tryDecode(data, width, height) {
    let code = jsQR(data.data, width, height, { inversionAttempts: 'attemptBoth' });
    if (code) return code;

    code = jsQR(adjustGamma(data, 0.8).data, width, height, { inversionAttempts: 'attemptBoth' });
    if (code) return code;

    code = jsQR(adjustGamma(data, 1.2).data, width, height, { inversionAttempts: 'attemptBoth' });
    if (code) return code;

    code = jsQR(thresholdImage(data, 160).data, width, height, { inversionAttempts: 'attemptBoth' });
    if (code) return code;

    code = jsQR(thresholdImage(data, 100).data, width, height, { inversionAttempts: 'attemptBoth' });
    return code;
  }

  qrScanInterval = setInterval(function () {
        if (video.readyState !== video.HAVE_ENOUGH_DATA) {
          return;
        }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = tryDecode(imageData, imageData.width, imageData.height);
        if (code) {
          $('#qr_code').val(code.data);
          $('#form_qr_code').val(code.data);
          setLocationStatus('QR scanned: ' + code.data, 'text-success');
          clearInterval(qrScanInterval);
          if (qrStream) {
            qrStream.getTracks().forEach(t => t.stop());
          }
          safeSetStartScan(false);
          safeSetStopScan(true);
        } else {
          $('#scanHint').text('Trying to read QR in various brightness modes...');
        }
      }, 500);
    })
    .catch(function (err) {
      alert('QR camera error: ' + err.message);
    });
}

function stopQRScan() {
  if (qrScanInterval) clearInterval(qrScanInterval);
  if (qrStream) {
    qrStream.getTracks().forEach(track => track.stop());
  }
  safeSetStartScan(false);
  safeSetStopScan(true);
  setLocationStatus('QR scanning stopped.');
}

$(function () {
  updateClock();
  setInterval(updateClock, 1000);

  // Initialize map first, then get location
  initMap();
  refreshGeolocation();
  setInterval(refreshGeolocation, 15000);

  // old start/stop buttons removed from UI; camera starts only when clicking LOG IN or LOG OUT.

  $('#dtrForm').on('submit', function () {
    const code = $('#qr_code').val().trim();
    if (!code) {
      alert('Please scan QR code before clocking in/out.');
      return false;
    }
    $('#form_qr_code').val(code);
    return true;
  });

  // Handle LOG IN/LOG OUT button clicks with automatic camera and form submission
  window.startLogInLogOut = function(button, action) {
    // Don't proceed if button is disabled
    if (button.disabled) return;

    // Reset existing QR values to force a fresh scan
    $('#qr_code').val('');
    $('#form_qr_code').val('');

    $('#scanHint').text('Starting camera... position QR code in frame.');

    // Stop any previous tracking before new scan
    if (qrScanInterval) {
      clearInterval(qrScanInterval);
    }
    if (qrStream) {
      qrStream.getTracks().forEach(t => t.stop());
    }

    // Start camera scan
    startQRScan();

    // Watch for QR code and auto-submit
    const checkInterval = setInterval(() => {
      const qrCode = $('#qr_code').val().trim();
      if (qrCode) {
        clearInterval(checkInterval);
        $('#scanHint').text('QR code captured. Finalizing log...');
        // Stop the camera
        stopQRScan();
        // Set the action and submit
        $('#dtr_action').val(action);
        $('#form_qr_code').val(qrCode);
        $('#form_geo_lat').val($('#geo_lat').val());
        $('#form_geo_lng').val($('#geo_lng').val());
        $('#dtrForm').submit();
      }
    }, 100);
  };
});

// Hide preloader after page loads
$(document).ready(function() {
  $('.preloader').fadeOut();

  $('#dtr-guide-btn').on('click', function() {
    $('#dtrGuideModal').modal('show');
  });
});

(function() {
  'use strict';

  if (!window.location.pathname.includes('/students/')) {
    return;
  }

  function pad(value) {
    return value < 10 ? '0' + value : String(value);
  }

  function updateClock() {
    var now = new Date();
    var time = now.toLocaleTimeString();
    var currentTime = document.getElementById('bigCurrentTime');
    var currentTimeLabel = document.getElementById('bigCurrentTimeLabel');
    var currentTimeSmall = document.getElementById('currentTime');
    var currentTimeSmallLabel = document.getElementById('currentTimeLabel');

    if (currentTime) {
      currentTime.textContent = time;
    }
    if (currentTimeLabel) {
      currentTimeLabel.textContent = 'Current local time: ' + time;
    }
    if (currentTimeSmall) {
      currentTimeSmall.textContent = time;
    }
    if (currentTimeSmallLabel) {
      currentTimeSmallLabel.textContent = 'Current time: ' + time;
    }

    var seconds = now.getSeconds();
    var rotation = seconds * 6;
    var watchIcon = document.getElementById('bigWatchIcon');
    if (watchIcon) {
      watchIcon.style.transform = 'rotate(' + rotation + 'deg)';
    }
  }

  function initAttendancePage() {
    var mapElement = document.getElementById('attendanceMap');
    if (!mapElement) {
      return;
    }

    var attendanceMap = null;
    var attendanceMapMarker = null;
    var attendanceGeocoder = null;
    var locationStatus = document.getElementById('attendanceLocationStatus');

    function initAttendanceMap() {
      if (attendanceMap || typeof google === 'undefined' || !google.maps) {
        return;
      }
      attendanceMap = new google.maps.Map(mapElement, {
        center: { lat: 12.8797, lng: 121.7740 },
        zoom: 6,
        mapTypeId: 'hybrid',
        disableDefaultUI: false,
        zoomControl: true,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
      });
      attendanceMapMarker = new google.maps.Marker({ map: attendanceMap });
      attendanceGeocoder = new google.maps.Geocoder();
    }

    function updateAttendanceMapLocation(lat, lng) {
      initAttendanceMap();
      if (!attendanceMap || !attendanceMapMarker) {
        return;
      }
      var pos = { lat: lat, lng: lng };
      attendanceMap.setCenter(pos);
      attendanceMap.setZoom(15);
      attendanceMapMarker.setPosition(pos);
      attendanceMapMarker.setTitle('Current location: ' + lat.toFixed(6) + ', ' + lng.toFixed(6));

      if (attendanceGeocoder) {
        attendanceGeocoder.geocode({ location: pos }, function(results, status) {
          if (status === 'OK' && results[0]) {
            attendanceMapMarker.setTitle(results[0].formatted_address);
            var infowindow = new google.maps.InfoWindow({ content: results[0].formatted_address });
            infowindow.open(attendanceMap, attendanceMapMarker);
          }
        });
      }
    }

    function refreshAttendanceGeolocation() {
      if (!locationStatus) {
        return;
      }
      if (!navigator.geolocation) {
        locationStatus.value = 'Geolocation not supported';
        return;
      }
      navigator.geolocation.getCurrentPosition(
        function(position) {
          var lat = position.coords.latitude;
          var lng = position.coords.longitude;
          locationStatus.value = 'Current location: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
          locationStatus.classList.remove('text-danger');
          locationStatus.classList.add('text-success');
          updateAttendanceMapLocation(lat, lng);
        },
        function(err) {
          locationStatus.value = 'Geolocation error: ' + err.message;
          locationStatus.classList.remove('text-success');
          locationStatus.classList.add('text-danger');
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 }
      );
    }

    initAttendanceMap();
    refreshAttendanceGeolocation();
  }

  function initDashboardPage() {
    var mapElement = document.getElementById('gmap');
    var videoElement = document.getElementById('qrVideo');
    var canvasElement = document.getElementById('qrCanvas');
    if (!mapElement || !videoElement || !canvasElement) {
      return;
    }

    var gmap = null;
    var mapMarker = null;
    var geocoder = null;
    var accuracyCircle = null;
    var qrScanInterval = null;
    var qrStream = null;
    var mapFallback = document.getElementById('gmapFallback');
    var scanHint = document.getElementById('scanHint');

    function showMapFallback(message) {
      if (mapElement) {
        mapElement.style.display = 'none';
      }
      if (mapFallback) {
        mapFallback.style.display = 'block';
        mapFallback.textContent = message || 'Google Maps is unavailable right now. Location will still be recorded, but the map is disabled.';
      }
    }

    function hideMapFallback() {
      if (mapElement) {
        mapElement.style.display = 'block';
      }
      if (mapFallback) {
        mapFallback.style.display = 'none';
      }
    }

    window.gm_authFailure = function() {
      showMapFallback('Google Maps authentication failed. The map cannot be displayed.');
    };

    function initMap() {
      if (gmap || typeof google === 'undefined' || !google.maps) {
        return;
      }

      if (window._gmAuthFailed) {
        showMapFallback('Google Maps authentication failed. The map cannot be displayed.');
        return;
      }

      try {
        gmap = new google.maps.Map(mapElement, {
          center: { lat: 12.8797, lng: 121.7740 },
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
      mapMarker = new google.maps.Marker({
        position: { lat: 12.8797, lng: 121.7740 },
        map: gmap,
        title: 'Your location',
        animation: google.maps.Animation.DROP,
        zIndex: 999,
      });
      accuracyCircle = new google.maps.Circle({
        strokeColor: '#4285F4',
        strokeOpacity: 0.3,
        strokeWeight: 1,
        fillColor: '#4285F4',
        fillOpacity: 0.1,
        map: gmap,
        center: { lat: 12.8797, lng: 121.7740 },
        radius: 100,
        zIndex: 998,
      });
      geocoder = new google.maps.Geocoder();
    }

    function setLocationStatus(text, type) {
      var el = $('#locationStatus');
      if (type === 'text-success') {
        if (scanHint) {
          scanHint.textContent = 'Location acquired, QR ready.';
        }
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
      $('.btn-login, .btn-logout').prop('disabled', isStarted);
      if (isStarted && scanHint) {
        scanHint.textContent = 'Scanning... please hold steady.';
      }
    }

    function safeSetStopScan(isStopped) {
      if (isStopped) {
        $('.btn-login, .btn-logout').prop('disabled', false);
        if (scanHint) {
          scanHint.textContent = 'Scan stopped. Tap LOG IN/LOG OUT to restart.';
        }
      }
    }

    function updateMapLocation(lat, lng, accuracy) {
      initMap();
      if (!gmap || typeof google === 'undefined' || !google.maps) {
        showMapFallback('Google Maps is unavailable. Location coordinates are still tracked.');
        return;
      }
      var pos = { lat: lat, lng: lng };
      gmap.setCenter(pos);
      gmap.setZoom(17);
      var radius = accuracy ? Math.max(50, accuracy * 3) : 100;

      if (mapMarker) {
        mapMarker.setPosition(pos);
        mapMarker.setTitle('Your location: ' + lat.toFixed(6) + ', ' + lng.toFixed(6));
      }
      if (accuracyCircle) {
        accuracyCircle.setCenter(pos);
        accuracyCircle.setRadius(radius);
      }
      if (geocoder) {
        geocoder.geocode({ location: pos }, function(results, status) {
          if (status === 'OK' && results[0]) {
            var address = results[0].formatted_address;
            if (mapMarker) {
              mapMarker.setTitle(address);
            }
            var infowindow = new google.maps.InfoWindow({
              content: '<div style="padding:8px; min-width:200px;">' +
                '<div style="margin-bottom:5px;"><i class="fas fa-map-marker-alt text-primary"></i> <strong>Your Location</strong></div>' +
                '<div style="color:#666; font-size:12px;">' + address + '</div>' +
                '<div style="margin-top:5px; padding-top:5px; border-top:1px solid #eee; font-size:11px; color:#999;">Accuracy: ±' + Math.round(accuracy) + 'm</div>' +
              '</div>',
            });
            setTimeout(function() {
              infowindow.open(gmap, mapMarker);
            }, 500);
          }
        });
      }
    }

    function refreshGeolocation() {
      if (!navigator.geolocation) {
        setLocationStatus('Geolocation not supported', 'text-danger');
        return;
      }
      setLocationStatus('Acquiring location...', 'text-muted');
      navigator.geolocation.getCurrentPosition(
        function(position) {
          var lat = position.coords.latitude;
          var lng = position.coords.longitude;
          var accuracy = position.coords.accuracy;
          $('#geo_lat').val(lat);
          $('#geo_lng').val(lng);
          $('#form_geo_lat').val(lat);
          $('#form_geo_lng').val(lng);
          var accuracyText = accuracy <= 10 ? 'High' : accuracy <= 50 ? 'Medium' : 'Low';
          setLocationStatus('📍 ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + ' (' + accuracyText + ' accuracy)', 'text-success');
          updateMapLocation(lat, lng, accuracy);
        },
        function(err) {
          var errorMsg = err.message;
          if (err.code === 1) {
            errorMsg = 'Location permission denied. Please enable location access.';
          } else if (err.code === 2) {
            errorMsg = 'Position unavailable. Try going near a window.';
          } else if (err.code === 3) {
            errorMsg = 'Location request timed out. Try again.';
          }
          setLocationStatus('⚠️ ' + errorMsg, 'text-danger');
        },
        { enableHighAccuracy: true, timeout: 30000, maximumAge: 60000 }
      );
    }

    function cloneImageData(src) {
      var dst = new ImageData(src.width, src.height);
      dst.data.set(src.data);
      return dst;
    }

    function adjustGamma(srcData, gamma) {
      var dst = cloneImageData(srcData);
      var invGamma = 1 / gamma;
      for (var i = 0; i < dst.data.length; i += 4) {
        dst.data[i] = 255 * Math.pow(dst.data[i] / 255, invGamma);
        dst.data[i + 1] = 255 * Math.pow(dst.data[i + 1] / 255, invGamma);
        dst.data[i + 2] = 255 * Math.pow(dst.data[i + 2] / 255, invGamma);
      }
      return dst;
    }

    function thresholdImage(srcData, thresh) {
      var dst = cloneImageData(srcData);
      for (var i = 0; i < dst.data.length; i += 4) {
        var gray = Math.round(0.299 * dst.data[i] + 0.587 * dst.data[i + 1] + 0.114 * dst.data[i + 2]);
        var val = gray >= thresh ? 255 : 0;
        dst.data[i] = dst.data[i + 1] = dst.data[i + 2] = val;
      }
      return dst;
    }

    function tryDecode(data, width, height) {
      var code = jsQR(data.data, width, height, { inversionAttempts: 'attemptBoth' });
      if (code) {
        return code;
      }
      code = jsQR(adjustGamma(data, 0.8).data, width, height, { inversionAttempts: 'attemptBoth' });
      if (code) {
        return code;
      }
      code = jsQR(adjustGamma(data, 1.2).data, width, height, { inversionAttempts: 'attemptBoth' });
      if (code) {
        return code;
      }
      code = jsQR(thresholdImage(data, 160).data, width, height, { inversionAttempts: 'attemptBoth' });
      if (code) {
        return code;
      }
      return jsQR(thresholdImage(data, 100).data, width, height, { inversionAttempts: 'attemptBoth' });
    }

    function startQRScan() {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Camera not available in this browser.');
        return;
      }
      var context = canvasElement.getContext('2d');
      navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
        .then(function(stream) {
          qrStream = stream;
          videoElement.srcObject = stream;
          videoElement.setAttribute('playsinline', true);
          videoElement.play();
          safeSetStartScan(true);
          safeSetStopScan(false);

          qrScanInterval = setInterval(function() {
            if (videoElement.readyState !== videoElement.HAVE_ENOUGH_DATA) {
              return;
            }
            canvasElement.width = videoElement.videoWidth;
            canvasElement.height = videoElement.videoHeight;
            context.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
            var imageData = context.getImageData(0, 0, canvasElement.width, canvasElement.height);
            var code = tryDecode(imageData, imageData.width, imageData.height);
            if (code) {
              $('#qr_code').val(code.data);
              $('#form_qr_code').val(code.data);
              setLocationStatus('QR scanned: ' + code.data, 'text-success');
              clearInterval(qrScanInterval);
              if (qrStream) {
                qrStream.getTracks().forEach(function(t) { t.stop(); });
              }
              safeSetStartScan(false);
              safeSetStopScan(true);
            } else if (scanHint) {
              scanHint.textContent = 'Trying to read QR in various brightness modes...';
            }
          }, 500);
        })
        .catch(function(err) {
          alert('QR camera error: ' + err.message);
        });
    }

    function stopQRScan() {
      if (qrScanInterval) {
        clearInterval(qrScanInterval);
      }
      if (qrStream) {
        qrStream.getTracks().forEach(function(track) {
          track.stop();
        });
      }
      safeSetStartScan(false);
      safeSetStopScan(true);
      setLocationStatus('QR scanning stopped.');
    }

    window.startLogInLogOut = function(button, action) {
      if (button.disabled) {
        return;
      }
      $('#qr_code').val('');
      $('#form_qr_code').val('');
      if (scanHint) {
        scanHint.textContent = 'Starting camera... position QR code in frame.';
      }
      if (qrScanInterval) {
        clearInterval(qrScanInterval);
      }
      if (qrStream) {
        qrStream.getTracks().forEach(function(t) { t.stop(); });
      }
      startQRScan();
      var watchInterval = setInterval(function() {
        var qrCode = $('#qr_code').val().trim();
        if (qrCode) {
          clearInterval(watchInterval);
          if (scanHint) {
            scanHint.textContent = 'QR code captured. Finalizing log...';
          }
          stopQRScan();
          $('#dtr_action').val(action);
          $('#form_qr_code').val(qrCode);
          $('#form_geo_lat').val($('#geo_lat').val());
          $('#form_geo_lng').val($('#geo_lng').val());
          $('#dtrForm').submit();
        }
      }, 100);
    };

    $(function() {
      updateClock();
      setInterval(updateClock, 1000);
      initMap();
      refreshGeolocation();
      setInterval(refreshGeolocation, 15000);

      $('#dtrForm').on('submit', function() {
        var code = $('#qr_code').val().trim();
        if (!code) {
          alert('Please scan QR code before clocking in/out.');
          return false;
        }
        $('#form_qr_code').val(code);
        return true;
      });

      $('.preloader').fadeOut();
      $('#dtr-guide-btn').on('click', function() {
        $('#dtrGuideModal').modal('show');
      });
    });
  }

  function initDocumentaryPage() {
    var openBtn = $('#open-daily-report-modal');
    if (!openBtn.length) {
      return;
    }

    function showReportStep(step) {
      $('#dailyReportStep1, #dailyReportStep2, #dailyReportStep3').hide();
      $('#dailyReportNext, #dailyReportPreview, #dailyReportSubmit').hide();
      $('#dailyReportBack').toggle(step !== 1);
      if (step === 1) {
        $('#dailyReportStep1').show();
        $('#dailyReportNext').show();
      } else if (step === 2) {
        $('#dailyReportStep2').show();
        $('#dailyReportPreview').show();
      } else {
        $('#dailyReportStep3').show();
        $('#dailyReportSubmit').show();
      }
    }

    function escapeHtml(text) {
      return $('<div>').text(text).html();
    }

    function renderReportPreview() {
      var note = $('#report_note').val().trim();
      var files = document.getElementById('report_images').files;
      var previewImages = $('#previewImages');
      previewImages.empty();

      if (files && files.length > 0) {
        Array.from(files).forEach(function(file) {
          if (!file.type.startsWith('image/')) {
            return;
          }
          var reader = new FileReader();
          reader.onload = function(e) {
            var img = $('<img>');
            img.attr('src', e.target.result);
            previewImages.append(img);
          };
          reader.readAsDataURL(file);
        });
      }

      var sanitized = note ? escapeHtml(note).replace(/\n/g, '<br>') : 'No report details entered.';
      $('#previewNote').html(sanitized);
    }

    openBtn.on('click', function() {
      $('#dailyReportModal').modal('show');
      $('#dailyReportStep1').show();
      $('#dailyReportStep2').hide();
      $('#dailyReportNext').show();
      $('#dailyReportSubmit').hide();
      $('#dailyReportBack').hide();
      $('#report_images').val('');
      $('#report_note').val('');
      $('#reportImagesError').addClass('d-none').text('');
      $('#reportNoteError').addClass('d-none').text('');
    });

    $('#dailyReportNext').on('click', function() {
      var fileInput = document.getElementById('report_images');
      var files = fileInput.files;
      var fileCount = files ? files.length : 0;
      if (fileCount < 1 || fileCount > 3) {
        $('#reportImagesError').removeClass('d-none').text('Please select between 1 and 3 pictures.');
        return;
      }
      $('#reportImagesError').addClass('d-none').text('');
      showReportStep(2);
    });

    $('#dailyReportPreview').on('click', function() {
      var note = $('#report_note').val().trim();
      if (!note) {
        $('#reportNoteError').removeClass('d-none').text('Please enter the report details before previewing.');
        return;
      }
      $('#reportNoteError').addClass('d-none').text('');
      renderReportPreview();
      showReportStep(3);
    });

    $('#dailyReportBack').on('click', function() {
      var isPreviewVisible = $('#dailyReportStep3').is(':visible');
      if (isPreviewVisible) {
        showReportStep(2);
      } else {
        showReportStep(1);
      }
    });

    $('#dailyReportForm').on('submit', function() {
      var note = $('#report_note').val().trim();
      if (!note) {
        $('#reportNoteError').removeClass('d-none').text('Please enter the report details before submitting.');
        return false;
      }
      return true;
    });

    $('.delete-entry-btn').on('click', function() {
      var entryId = $(this).data('id');
      if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
        var form = $('<form method="post">');
        form.append('<input type="hidden" name="delete_id" value="' + entryId + '">');
        form.append('<input type="hidden" name="entry_date" value="' + document.querySelector('[name="entry_date"]').value + '">');
        $('body').append(form);
        form.submit();
      }
    });
  }

  function initLogbookPage(config) {
    if (!document.getElementById('logbook-calendar-grid') || !config) {
      return;
    }

    var calendarMonth = parseInt(config.calendarMonth, 10);
    var calendarYear = parseInt(config.calendarYear, 10);
    var selectedDate = config.selectedDate || '';
    var entriesByDate = config.entriesByDate || {};
    var studentDepartment = config.studentDepartment || '';

    function getDepartmentTheme(dept) {
      if (dept.includes('Information Systems') || dept.includes('Computer Science')) {
        return { bg: 'purple', text: 'white' };
      }
      if (dept.includes('Business Administration') || dept.includes('Human Resource') || dept.includes('Agriculture Business')) {
        return { bg: 'yellow', text: 'black' };
      }
      if (dept.includes('Criminal Justice')) {
        return { bg: 'red', text: 'white' };
      }
      if (dept.includes('Agricultural Biosystem Engineering')) {
        return { bg: 'orange', text: 'white' };
      }
      if (dept.includes('BSED') || dept.includes('BEED')) {
        return { bg: 'blue', text: 'white' };
      }
      if (dept.includes('Forestry') || dept.includes('Agriculture')) {
        return { bg: 'green', text: 'white' };
      }
      return { bg: '#2f4b6f', text: '#2f4b6f' };
    }

    function formatDateYMD(d) {
      var m = d.getMonth() + 1;
      var day = d.getDate();
      return d.getFullYear() + '-' + pad(m) + '-' + pad(day);
    }

    function renderNow() {
      var now = new Date();
      var nowString = now.toLocaleString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
      var todayText = 'Today: ' + now.toISOString().slice(0, 10);
      var nowEl = document.getElementById('lb-now');
      var todayInfoEl = document.getElementById('lb-today-info');
      if (nowEl) {
        nowEl.textContent = nowString;
      }
      if (todayInfoEl) {
        todayInfoEl.textContent = todayText + (selectedDate === now.toISOString().slice(0, 10) ? ' (Selected)' : '');
      }
    }

    function renderCalendar() {
      var first = new Date(calendarYear, calendarMonth - 1, 1);
      var start = new Date(first);
      var dayOfWeek = start.getDay();
      start.setDate(start.getDate() - dayOfWeek);
      var titleEl = document.getElementById('lb-month-year');
      if (titleEl) {
        titleEl.textContent = first.toLocaleString('en-US', { month: 'long', year: 'numeric' });
      }
      var tableHtml = '<table class="table table-sm table-bordered mb-0 calendar-table"><thead><tr>' +
        ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].map(function(w) { return '<th class="text-center">' + w + '</th>'; }).join('') +
        '</tr></thead><tbody>';
      var current = new Date(start);
      var todayStr = formatDateYMD(new Date());

      for (var week = 0; week < 6; week++) {
        tableHtml += '<tr>';
        for (var dow = 0; dow < 7; dow++) {
          var inCurr = current.getMonth() === (calendarMonth - 1);
          var dateKey = formatDateYMD(current);
          var dayData = entriesByDate[dateKey] || {};
          var entryInfo = '';
          if (dayData.entries && dayData.entries.length > 0) {
            entryInfo = '<div class="day-info">' + dayData.entries.length + ' log' + (dayData.entries.length > 1 ? 's' : '') + ' • ' + Number(dayData.hours).toFixed(1) + 'h</div>';
          }
          var active = dateKey === selectedDate ? ' border border-primary' : '';
          var weekend = (dow === 0 || dow === 6) ? ' bg-light' : '';
          var todayHighlight = dateKey === todayStr ? ' bg-info' : '';
          var classNames = 'text-center align-top' + (entryInfo ? ' has-entries' : '');
          var cellStyle = 'padding: 0; vertical-align: top; position: relative; border-radius: 12px; box-shadow: 0 2px 8px var(--student-calendar-shadow); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background-color: ' + (inCurr ? 'var(--student-calendar-card-bg)' : 'var(--student-calendar-other-month-bg)') + '; border: 2px solid transparent;';
          if (weekend) { cellStyle += ' background: var(--student-calendar-weekend-bg);'; }
          if (todayHighlight) { cellStyle += ' background: var(--student-calendar-today-bg); border-color: #007bff; transform: scale(1.05);'; }
          if (active) { cellStyle += ' border-color: #28a745; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);'; }

          tableHtml += '<td class="' + classNames + '" data-date="' + dateKey + '" style="cursor:pointer; ' + cellStyle + '" onmouseover="this.style.transform=\'scale(1.02)\'; this.style.boxShadow=\'0 6px 20px var(--student-calendar-hover-shadow)\'; this.style.zIndex=\'10\';" onmouseout="this.style.transform=\'' + (todayHighlight ? 'scale(1.05)' : 'scale(1)') + '\'; this.style.boxShadow=\'0 2px 8px var(--student-calendar-shadow)\'; this.style.zIndex=\'1\';">' +
            '<div class="calendar-day-cell">' +
              '<div class="day-number" style="color:' + (inCurr ? 'var(--student-calendar-text)' : 'var(--student-calendar-text-muted)') + ';">' + current.getDate() + '</div>' +
              entryInfo +
            '</div>' +
          '</td>';
          current.setDate(current.getDate() + 1);
        }
        tableHtml += '</tr>';
      }
      tableHtml += '</tbody></table>';
      document.getElementById('logbook-calendar-grid').innerHTML = tableHtml;
      document.querySelectorAll('#logbook-calendar-grid td[data-date]').forEach(function(td) {
        td.addEventListener('click', function() {
          var date = this.getAttribute('data-date');
          if (date) {
            showLogbookModal(date);
          }
        });
      });
    }

    function showLogbookModal(date) {
      var modalDateEl = document.getElementById('modal-date');
      var modalBodyEl = document.getElementById('modal-body');
      var dayData = entriesByDate[date] || {};
      if (modalDateEl) {
        modalDateEl.textContent = date;
      }
      var html = '';
      if (dayData.entries && dayData.entries.length > 0) {
        var totalSeconds = dayData.seconds || 0;
        var totalHours = dayData.hours || 0;
        var totalH = Math.floor(totalSeconds / 3600);
        var totalM = Math.floor((totalSeconds % 3600) / 60);
        var totalS = totalSeconds % 60;
        html += '<div class="logbook-summary"><strong>Total logged:</strong> ' + pad(totalH) + ':' + pad(totalM) + ':' + pad(totalS) + ' (' + Number(totalHours).toFixed(2) + 'h)</div>';
        html += '<div class="logbook-documentary"><h6>Daily Logged Time</h6>';
        dayData.entries.forEach(function(entry, idx) {
          var rawSeconds = entry.raw_seconds || Math.round((entry.hours || 0) * 3600);
          var entryH = Math.floor(rawSeconds / 3600);
          var entryM = Math.floor((rawSeconds % 3600) / 60);
          var entryS = rawSeconds % 60;
          var inTime = entry.in ? new Date(entry.in * 1000).toLocaleString() : 'n/a';
          var outTime = entry.out ? new Date(entry.out * 1000).toLocaleString() : 'n/a';
          html += '<div class="entry-item">';
          html += '<div><strong>Log ' + (idx + 1) + '</strong> <small>(' + Number(entry.hours || 0).toFixed(2) + 'h / ' + Math.floor(rawSeconds / 60) + 'm ' + entryS + 's)</small></div>';
          html += '<div><small>In: </small><strong>' + inTime + '</strong></div>';
          html += '<div><small>Out: </small><strong>' + outTime + '</strong></div>';
          html += '<div><small>Duration: </small>' + pad(entryH) + ':' + pad(entryM) + ':' + pad(entryS) + '</div>';
          html += '</div>';
        });
        html += '</div>'; 
      } else if (dayData.hours > 0) {
        var totalSeconds = dayData.seconds || Math.round((dayData.hours || 0) * 3600);
        var totalHours = dayData.hours || 0;
        var totalH = Math.floor(totalSeconds / 3600);
        var totalM = Math.floor((totalSeconds % 3600) / 60);
        var totalS = totalSeconds % 60;
        html += '<div class="logbook-summary"><strong>Total logged:</strong> ' + pad(totalH) + ':' + pad(totalM) + ':' + pad(totalS) + ' (' + Number(totalHours).toFixed(2) + 'h)</div>';
        html += '<div class="logbook-documentary"><h6>Daily Logged Time</h6>';
        if (dayData.entries && dayData.entries.length > 0) {
          dayData.entries.forEach(function(entry, idx) {
            var rawSeconds = entry.raw_seconds || Math.round((entry.hours || 0) * 3600);
            var eH = Math.floor(rawSeconds / 3600);
            var eM = Math.floor((rawSeconds % 3600) / 60);
            var eS = rawSeconds % 60;
            var inTime = entry.in ? new Date(entry.in * 1000).toLocaleString() : 'n/a';
            var outTime = entry.out ? new Date(entry.out * 1000).toLocaleString() : 'n/a';
            html += '<div class="entry-item">';
            html += '<strong>Log ' + (idx + 1) + ':</strong> ' + pad(eH) + ':' + pad(eM) + ':' + pad(eS) + ' (' + Number(entry.hours || 0).toFixed(2) + 'h)<br>';
            html += '<small>In: ' + inTime + '</small><br>';
            html += '<small>Out: ' + outTime + '</small>';
            html += '</div>';
          });
        } else {
          html += '<div class="entry-item"><strong>In:</strong> n/a<br><strong>Out:</strong> n/a<br><small>Recorded: ' + pad(totalH) + ':' + pad(totalM) + ':' + pad(totalS) + ' (' + Number(totalHours).toFixed(2) + 'h)</small></div>';
        }
        html += '</div>';
      } else {
        html += '<div class="alert alert-warning">No entries for ' + date + ' yet.</div>';
      }
      if (modalBodyEl) {
        modalBodyEl.innerHTML = html;
      }
      var theme = getDepartmentTheme(studentDepartment);
      var headerEl = document.querySelector('#logbookModal .modal-header');
      if (headerEl) {
        headerEl.style.background = 'linear-gradient(120deg, ' + theme.bg + ', ' + theme.bg + ')';
        headerEl.style.color = theme.text;
      }
      $('#logbookModal').modal('show');
    }

    function attachNavigation(buttonId, callback) {
      var button = document.getElementById(buttonId);
      if (!button) {
        return;
      }
      button.addEventListener('click', callback);
    }

    attachNavigation('lb-prev-year', function() {
      calendarYear -= 1;
      selectedDate = formatDateYMD(new Date(calendarYear, calendarMonth - 1, 1));
      window.location.href = window.location.pathname + '?month=' + calendarMonth + '&year=' + calendarYear + '&day=' + encodeURIComponent(selectedDate);
    });

    attachNavigation('lb-prev-month', function() {
      calendarMonth -= 1;
      if (calendarMonth < 1) {
        calendarMonth = 12;
        calendarYear -= 1;
      }
      selectedDate = formatDateYMD(new Date(calendarYear, calendarMonth - 1, 1));
      window.location.href = window.location.pathname + '?month=' + calendarMonth + '&year=' + calendarYear + '&day=' + encodeURIComponent(selectedDate);
    });

    attachNavigation('lb-next-month', function() {
      calendarMonth += 1;
      if (calendarMonth > 12) {
        calendarMonth = 1;
        calendarYear += 1;
      }
      selectedDate = formatDateYMD(new Date(calendarYear, calendarMonth - 1, 1));
      window.location.href = window.location.pathname + '?month=' + calendarMonth + '&year=' + calendarYear + '&day=' + encodeURIComponent(selectedDate);
    });

    attachNavigation('lb-next-year', function() {
      calendarYear += 1;
      selectedDate = formatDateYMD(new Date(calendarYear, calendarMonth - 1, 1));
      window.location.href = window.location.pathname + '?month=' + calendarMonth + '&year=' + calendarYear + '&day=' + encodeURIComponent(selectedDate);
    });

    attachNavigation('lb-today', function() {
      var today = new Date();
      calendarMonth = today.getMonth() + 1;
      calendarYear = today.getFullYear();
      selectedDate = formatDateYMD(today);
      window.location.href = window.location.pathname + '?month=' + calendarMonth + '&year=' + calendarYear + '&day=' + encodeURIComponent(selectedDate);
    });

    attachNavigation('lb-refresh', function() {
      var url = '?api=logbook_calendar&month=' + calendarMonth + '&year=' + calendarYear;
      fetch(url, { cache: 'no-cache' })
        .then(function(response) { return response.json(); })
        .then(function(data) {
          if (data && data.entriesByDate) {
            entriesByDate = data.entriesByDate;
            selectedDate = data.selectedDate || selectedDate;
            renderCalendar();
          }
        });
    });

    renderNow();
    renderCalendar();
    setInterval(renderNow, 1000);
  }

  function initProfilePage() {
    var avatarInput = document.getElementById('userAvatar');
    if (!avatarInput) {
      return;
    }
    $(window).on('load', function() {
      $('.preloader').fadeOut();
    });
    $(document).ready(function() {
      $('.preloader').fadeOut();
    });

    avatarInput.addEventListener('change', function(event) {
      var file = event.target.files[0];
      if (!file) {
        return;
      }
      var reader = new FileReader();
      reader.onload = function(e) {
        var container = document.querySelector('.form-group.text-center');
        if (!container) {
          return;
        }
        var img = container.querySelector('img');
        if (!img) {
          var div = container.querySelector('div.img-circle');
          if (div) {
            img = document.createElement('img');
            img.className = 'img-circle elevation-2';
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.alt = 'Profile Image';
            div.parentNode.replaceChild(img, div);
          }
        }
        if (img) {
          img.src = e.target.result;
        }
      };
      reader.readAsDataURL(file);
    });
  }

  function initQrPage() {
    var qrContainer = document.getElementById('qrcode');
    if (!qrContainer) {
      return;
    }
    if (typeof QRCode === 'undefined') {
      return;
    }

    var studentId = qrContainer.dataset.studentId || window.studentId || '';
    new QRCode(qrContainer, {
      text: studentId,
      width: 210,
      height: 210,
      colorDark: '#000000',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });

    var downloadBtn = document.getElementById('downloadBtn');
    if (downloadBtn) {
      downloadBtn.addEventListener('click', function() {
        var img = qrContainer.querySelector('img');
        if (!img || !img.src) {
          alert('QR code not ready yet.');
          return;
        }
        var link = document.createElement('a');
        link.href = img.src;
        link.download = 'student-qr-' + String(studentId).replace(/[^A-Za-z0-9]+/g, '-') + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }
  }

  function initSettingsPage() {
    var showQrBtn = document.getElementById('showQrBtn');
    var qrCodeHolder = document.getElementById('qrCodeHolder');
    var downloadQrBtn = document.getElementById('downloadQrBtn');
    if (!showQrBtn || !qrCodeHolder) {
      return;
    }
    if (typeof QRCode === 'undefined') {
      return;
    }

    function renderStudentQr() {
      qrCodeHolder.innerHTML = '';
      var code = new QRCode(qrCodeHolder, {
        text: window.studentId || 'UNKNOWN',
        width: 220,
        height: 220,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
      });
      var idLabel = document.getElementById('qrStudentId');
      if (idLabel) {
        idLabel.textContent = window.studentId || '';
      }
      return code;
    }

    showQrBtn.addEventListener('click', function() {
      renderStudentQr();
      $('#qrModal').modal('show');
    });

    if (downloadQrBtn) {
      downloadQrBtn.addEventListener('click', function() {
        var img = qrCodeHolder.querySelector('img');
        if (!img || !img.src) {
          alert('QR code is not ready yet.');
          return;
        }
        var link = document.createElement('a');
        link.href = img.src;
        link.download = 'student-qr-' + String(window.studentId || '').replace(/[^A-Za-z0-9]+/g, '-') + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    initAttendancePage();
    initDashboardPage();
    initDocumentaryPage();
    initProfilePage();
    initQrPage();
    initSettingsPage();
    if (window.studentLogbookData) {
      initLogbookPage(window.studentLogbookData);
    }
  });
})();

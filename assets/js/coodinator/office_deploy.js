// Office Deploy Page Scripts

let map;
let marker;
let selectedOffice = null;
let deploymentMarkers = [];

// Global callback for Google Maps auth failure
window.gm_authFailure = function() {
  document.getElementById('map').style.display = 'none';
  document.getElementById('map-fallback').style.display = 'block';
};

function clearDeploymentMarkers() {
  deploymentMarkers.forEach(m => m.setMap(null));
  deploymentMarkers = [];
}

function showDeployedStudents(locationKey) {
  const decodedKey = decodeURIComponent(locationKey);
  const infoEl = document.getElementById('deployedStudentsInfo');
  if (!infoEl || !window.deployedLocationGroups) return;
  const names = window.deployedLocationGroups[decodedKey] || [];
  infoEl.innerHTML = '<div class="alert alert-info py-2">' +
    '<strong>Students at this location:</strong><br>' +
    names.map(n => '<span class="badge badge-pill badge-primary mr-1">' + n + '</span>').join(' ') +
    '</div>';
}

function initMap() {
  try {
    if (!map) {
      map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 12.8797, lng: 121.7740 },
        zoom: 6,
        mapTypeId: 'hybrid',
        fullscreenControl: true,
        streetViewControl: false,
        mapTypeControl: false,
      });

      marker = new google.maps.Marker({
        position: { lat: 12.8797, lng: 121.7740 },
        map: map,
        draggable: true,
        visible: false,
      });

      map.addListener('click', function(e) {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();
        setUserLocation(lat, lng, 'Selected office location', 'Lat: ' + lat.toFixed(6) + ', Lng: ' + lng.toFixed(6));
      });

      marker.addListener('dragend', function() {
        const pos = marker.getPosition();
        selectedOffice = { name: 'Custom dragged position', address: '', lat: pos.lat(), lng: pos.lng() };
        updateSelectedOffice();
      });
    }
  } catch (e) {
    console.error('Map initialization failed:', e);
    document.getElementById('map').style.display = 'none';
    document.getElementById('map-fallback').style.display = 'block';
  }
}

function updateSelectedOffice() {
  if (!selectedOffice) { return; }
  document.getElementById('selectedLocation').value = selectedOffice.name + (selectedOffice.address ? ' - ' + selectedOffice.address : '');
  document.getElementById('selectedLat').value = selectedOffice.lat.toFixed(6);
  document.getElementById('selectedLng').value = selectedOffice.lng.toFixed(6);
  document.getElementById('deployStudentBtn').disabled = false;
}

function setUserLocation(lat, lng, label, addr) {
  if (!map) return;
  const position = { lat, lng };
  map.setCenter(position);
  map.setZoom(16);
  marker.setPosition(position);
  marker.setVisible(true);
  selectedOffice = { name: label, address: addr || '', lat, lng };
  updateSelectedOffice();
}

function renderAssignedStudents(list) {
  const summary = document.getElementById('assignedStudentsSummary');
  const loadingEl = document.getElementById('assignedStudentsLoading');
  const countBadge = document.getElementById('assignedCountBadge');
  
  if (loadingEl) loadingEl.style.display = 'none';
  if (summary) summary.style.display = 'block';
  
  if (!Array.isArray(list) || !list.length) {
    if (summary) {
      const btn = '<a href="assigned_students.php" class="btn btn-info btn-block disabled" tabindex="-1" role="button" aria-disabled="true">' +
                  '<i class="fas fa-eye mr-1"></i> View All Assigned Students</a>';
      summary.innerHTML = '<p class="mb-3 text-muted"><i class="fas fa-info-circle mr-1"></i>No students assigned yet. Deploy a student first to see assignments.</p>' + btn;
    }
    if (countBadge) countBadge.textContent = '0';
    clearDeploymentMarkers();
    window.deployedLocationGroups = {};
    return;
  }

  if (countBadge) countBadge.textContent = list.length;
  if (summary) {
    summary.innerHTML = '<p class="mb-3 text-muted">Assigned students are displayed on the map above and grouped by location.</p>' +
                        '<a href="assigned_students.php" class="btn btn-info btn-block">' +
                        '<i class="fas fa-eye mr-1"></i> View All Assigned Students</a>';
  }

  renderDeploymentMarkers(list);
}

function renderDeploymentMarkers(assignments) {
  clearDeploymentMarkers();
  const groups = {};

  assignments.forEach(item => {
    const key = item.location_name + '|' + item.lat.toFixed(6) + '|' + item.lng.toFixed(6);
    if (!groups[key]) groups[key] = { lat: item.lat, lng: item.lng, location: item.location_name, students: [] };
    groups[key].students.push(item.student_name);
  });

  window.deployedLocationGroups = {};

  Object.keys(groups).forEach(key => {
    const group = groups[key];
    window.deployedLocationGroups[key] = group.students;

    const m = new google.maps.Marker({
      position: { lat: group.lat, lng: group.lng },
      map: map,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 18,
        fillColor: '#31708f',
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 2,
      },
      label: {
        text: String(group.students.length),
        color: '#fff',
        fontWeight: '700',
      }
    });

    const encodedKey = encodeURIComponent(key);
    const popupHtml = '<strong>' + group.location + '</strong><br>' +
                      '<small>(' + group.lat.toFixed(6) + ', ' + group.lng.toFixed(6) + ')</small><br>' +
                      '<button class="btn btn-sm btn-outline-primary mt-1" onclick="showDeployedStudents(\'' + encodedKey + '\')">Show deployed student(s)</button>';
    const markerInfo = new google.maps.InfoWindow({ content: popupHtml });

    m.addListener('click', function() {
      markerInfo.open(map, m);
      map.setCenter(m.getPosition());
      map.setZoom(18);
    });

    deploymentMarkers.push(m);
  });
}

function undeployStudent(studentId, studentName) {
  if (!studentId) return;

  if (!confirm('Undeploy ' + studentName + '? This will remove the assigned location.')) {
    return;
  }

  $.ajax({
    url: 'save_office_assignment.php',
    method: 'POST',
    dataType: 'json',
    data: {
      action: 'undeploy',
      student_id: studentId
    },
    success(data) {
      if (data && data.success) {
        toastr.success(data.message || 'Student undeployed');
        if (Array.isArray(data.assignments)) {
          renderAssignedStudents(data.assignments);
        } else {
          refreshAssignedStudents();
        }
      } else {
        toastr.error(data.message || 'Undeploy failed');
      }
    },
    error() {
      toastr.error('Undeploy failed');
    }
  });
}

function showOfficeDeployOverlay(message) {
  const overlay = document.getElementById('officeDeployOverlay');
  if (!overlay) return;
  overlay.querySelector('.overlay-text').textContent = message || 'Updating assignments…';
  overlay.style.display = 'flex';
}

function hideOfficeDeployOverlay() {
  const overlay = document.getElementById('officeDeployOverlay');
  if (!overlay) return;
  overlay.style.display = 'none';
}

function refreshAssignedStudents() {
  const summary = document.getElementById('assignedStudentsSummary');
  const loadingEl = document.getElementById('assignedStudentsLoading');
  if (loadingEl) loadingEl.style.display = 'block';
  if (summary) summary.style.display = 'none';

  showOfficeDeployOverlay('Refreshing assignments...');

  $.ajax({
    url: 'save_office_assignment.php',
    method: 'GET',
    dataType: 'json',
    cache: false,
    success(data) {
      if (data && data.success) {
        renderAssignedStudents(data.assignments || []);
      } else {
        renderAssignedStudents([]);
      }
    },
    error() {
      renderAssignedStudents([]);
    },
    complete() {
      hideOfficeDeployOverlay();
    }
  });
}

function geocodeOffice(query) {
  const searchResults = document.getElementById('searchResults');
  searchResults.innerHTML = '<li class="list-group-item">Searching…</li>';

  fetch('https://nominatim.openstreetmap.org/search?format=json&limit=8&q=' + encodeURIComponent(query), {
    headers: { 'Accept': 'application/json' }
  })
  .then(res => res.json())
  .then(data => {
    searchResults.innerHTML = '';
    if (!Array.isArray(data) || data.length === 0) {
      searchResults.innerHTML = '<li class="list-group-item text-muted">No results found.</li>';
      return;
    }
    data.forEach(loc => {
      const li = document.createElement('li');
      li.className = 'list-group-item list-group-item-action';
      const nameParts = loc.display_name.split(',');
      const shortName = nameParts[0].trim();
      const fullAddress = nameParts.slice(1).join(',').trim();
      li.innerHTML = '<div class="d-flex justify-content-between align-items-center"><div><strong>' + shortName + '</strong><br><small class="text-muted">' + (fullAddress || loc.display_name) + '</small></div><i class="fas fa-chevron-right text-muted"></i></div>';
      li.style.cursor = 'pointer';
      li.addEventListener('click', function() {
        setUserLocation(parseFloat(loc.lat), parseFloat(loc.lon), shortName, loc.display_name);
        searchResults.innerHTML = '';
      });
      searchResults.appendChild(li);
    });
  })
  .catch(err => {
    console.error(err);
    searchResults.innerHTML = '<li class="list-group-item text-danger">Search failed. Try again.</li>';
  });
}

// ===== Debounce function for secure search =====
function debounce(func, delay) {
  let timer;
  return function() {
    const args = arguments;
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, args), delay);
  };
}

// ===== Secure & debounced search handler =====
function filterStudents() {
  const query = $('#studentModalSearch').val().trim().toLowerCase();
  // Escape special regex characters to prevent injection
  const safeQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  $('#studentSelectionTable tbody tr').each(function() {
    const name = $(this).data('student-name').toLowerCase();
    const match = safeQuery === '' || name.indexOf(safeQuery) !== -1;
    $(this).toggle(match);
  });
}

$(document).ready(function() {
  initMap();
  refreshAssignedStudents();
  updateSelectionButton();

  function updateModalSelectedCount() {
    const count = $('.student-checkbox:checked').length;
    $('#selectedModalCount').text('Selected: ' + count);
  }

  function syncStudentModalSelection() {
    const selectedValues = $('#studentSelect').val() || [];
    $('.student-checkbox').each(function() {
      const row = $(this).closest('tr');
      const id = row.data('student-id');
      const isDeployed = row.data('deployed') == 1;
      if (isDeployed) {
        $(this).prop('disabled', true).prop('checked', false);
      } else {
        $(this).prop('disabled', false).prop('checked', selectedValues.includes(id));
      }
    });
    updateModalSelectedCount();
    $('#selectAllStudents').prop('checked', false);
  }

  function updateSelectionButton() {
    const selectedValues = $('#studentSelect').val() || [];
    const button = $('#openStudentSelectionBtn');
    if (selectedValues.length > 0) {
      button.html('<i class="fas fa-users mr-1"></i> ' + selectedValues.length + ' student(s) selected');
    } else {
      button.html('<i class="fas fa-users mr-1"></i> Select Students');
    }
  }

  // ---- Search with debounce (300ms) ----
  $('#studentModalSearch').on('keyup', debounce(filterStudents, 300));

  // Clear search
  $('#clearStudentSearch').on('click', function() {
    $('#studentModalSearch').val('').trigger('keyup');
    $('#studentModalSearch').focus();
  });

  // ---- Modal open ----
  $('#openStudentSelectionBtn').on('click', function() {
    syncStudentModalSelection();
    $('#studentSelectionModal').modal('show');
  });

  // ---- Select All ----
  $('#selectAllStudents').on('change', function() {
    const checked = $(this).prop('checked');
    $('.student-checkbox:not(:disabled)').prop('checked', checked);
    updateModalSelectedCount();
  });

  // ---- Individual checkbox change ----
  $(document).on('change', '.student-checkbox', function() {
    updateModalSelectedCount();
    const total = $('.student-checkbox:not(:disabled)').length;
    const checked = $('.student-checkbox:checked').length;
    $('#selectAllStudents').prop('checked', total > 0 && checked === total);
  });

  // ---- Save selection ----
  $('#saveStudentSelectionBtn').on('click', function() {
    const selectedIds = [];
    $('.student-checkbox:checked').each(function() {
      const row = $(this).closest('tr');
      selectedIds.push(row.data('student-id'));
    });
    $('#studentSelect').val(selectedIds).trigger('change');
    updateSelectionButton();
    $('#studentSelectionModal').modal('hide');
    if (selectedIds.length > 0) {
      toastr.success(selectedIds.length + ' student(s) selected');
    } else {
      toastr.info('No students selected');
    }
  });

  // ---- Clear location button ----
  $('#clearLocationBtn').on('click', function() {
    selectedOffice = null;
    $('#selectedLocation').val('');
    $('#selectedLat').val('');
    $('#selectedLng').val('');
    $('#deployStudentBtn').prop('disabled', true);
    if (marker) marker.setVisible(false);
    toastr.info('Location cleared');
  });

  // ---- Update student count badge ----
  $('#studentSelect').on('change', function() {
    const selected = $(this).val() || [];
    const count = selected.length;
    const badge = $('#selectedCountBadge');
    if (count > 0) {
      badge.text(count).show();
    } else {
      badge.hide();
    }
    const infoDiv = $('#selectedStudentsInfo');
    const countSpan = $('#selectedStudentsCount');
    if (count > 0) {
      countSpan.text(count);
      infoDiv.show();
    } else {
      infoDiv.hide();
    }
    updateDeployButtonState();
  });

  // ---- Collapse toggle icons ----
  $('#deployStudentCollapse').on('shown.bs.collapse', function() {
    $('#deployCollapseIcon').removeClass('fa-plus').addClass('fa-minus');
  }).on('hidden.bs.collapse', function() {
    $('#deployCollapseIcon').removeClass('fa-minus').addClass('fa-plus');
  });

  // ---- Deploy button state ----
  function updateDeployButtonState() {
    const hasStudents = ($('#studentSelect').val() || []).length > 0;
    const hasLocation = selectedOffice && selectedOffice.lat && selectedOffice.lng;
    $('#deployStudentBtn').prop('disabled', !(hasStudents && hasLocation));
  }

  // ---- Intercept location selection ----
  const originalUpdateSelectedOffice = updateSelectedOffice;
  window.updateSelectedOffice = function() {
    originalUpdateSelectedOffice();
    updateDeployButtonState();
  };

  // ---- Deployment guide ----
  $('#deployment-guide-btn').on('click', function() {
    $('#deploymentGuideModal').modal('show');
  });

  // ---- Undeploy button (dynamic) ----
  $(document).on('click', '.undeploy-btn', function() {
    const studentId = $(this).data('student-id');
    const studentName = $(this).data('student-name') || 'student';
    undeployStudent(studentId, studentName);
  });

  // ---- Office search ----
  $('#searchOfficeBtn').on('click', function() {
    const q = $('#officeSearch').val().trim();
    if (!q) return;
    geocodeOffice(q);
  });

  $('#officeSearch').on('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      $('#searchOfficeBtn').click();
    }
  });

  // ---- Deploy result modal (if missing) ----
  if ($('#deployResultModal').length === 0) {
    $('body').append(`
      <div class="modal fade" id="deployResultModal" tabindex="-1" role="dialog" aria-labelledby="deployResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="deployResultModalLabel">Assignment Status</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" id="deployResultModalBody"></div>
          </div>
        </div>
      </div>
    `);
  }

  // ---- Deploy button ----
  $('#deployStudentBtn').on('click', function() {
    const studentIds = $('#studentSelect').val();
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');

    if (!studentIds || studentIds.length === 0) {
      sessionStorage.setItem('deployAlert', JSON.stringify({type: 'danger', msg: 'Please select at least one student first.'}));
      location.reload();
      return;
    }
    if (!selectedOffice || !selectedOffice.lat || !selectedOffice.lng) {
      sessionStorage.setItem('deployAlert', JSON.stringify({type: 'danger', msg: 'Please select a location on the map first.'}));
      location.reload();
      return;
    }

    $.ajax({
      url: 'save_office_assignment.php',
      method: 'POST',
      dataType: 'json',
      data: {
        student_ids: JSON.stringify(studentIds),
        location_name: selectedOffice.name,
        location_address: selectedOffice.address || selectedOffice.name,
        lat: selectedOffice.lat,
        lng: selectedOffice.lng
      },
      success(data) {
        if (data && data.success) {
          sessionStorage.setItem('deployAlert', JSON.stringify({type: 'success', msg: 'Successfully deployed student(s) to ' + selectedOffice.name + '.'}));
          location.reload();
        } else {
          sessionStorage.setItem('deployAlert', JSON.stringify({type: 'danger', msg: (data && data.message) ? data.message : 'Assign failed'}));
          location.reload();
        }
      },
      error: function() {
        sessionStorage.setItem('deployAlert', JSON.stringify({type: 'danger', msg: 'Assign failed. Please try again.'}));
        location.reload();
      }
    });
  });

  // ---- Show deploy result alert if exists ----
  const deployAlert = sessionStorage.getItem('deployAlert');
  if (deployAlert) {
    try {
      const alertObj = JSON.parse(deployAlert);
      if (alertObj && alertObj.type && alertObj.msg) {
        sessionStorage.removeItem('deployAlert');
        const icon = alertObj.type === 'success'
          ? '<span class="text-success mr-2"><i class="fas fa-check-circle fa-lg"></i></span>'
          : '<span class="text-danger mr-2"><i class="fas fa-times-circle fa-lg"></i></span>';
        $('#deployResultModalBody').html('<div class="d-flex align-items-center">' + icon + '<span>' + $('<div>').text(alertObj.msg).html() + '</span></div>');
        $('#deployResultModal').modal('show');
        setTimeout(function() {
          $('#deployResultModal').modal('hide');
        }, 5000);
      }
    } catch (e) { sessionStorage.removeItem('deployAlert'); }
  }

});

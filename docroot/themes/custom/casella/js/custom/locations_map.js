// Display the map/results toggles.
if (jQuery('.locationMap-listView-container').length) {
  jQuery('.panelControllers').show();
}

// Exposing the map as a global so it can be used by the zozo init.
var casellaMap, casellaMapCenter, nameArg;

function initMap() {
  var mapOptions,
      mapInfo = {map: {}, markers: {all: [], allIndexed: {}}, bounds: {}, infoWindows: {}, iconPaths: getIconPaths(), clusterer: {}},
      nameArg;

  casellaMapCenter = new google.maps.LatLng(43.687239, -72.301025);

  //create the map variables
  var mapOptions = {
    zoom: 6,
    mapTypeControl: true,
    center: casellaMapCenter,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    scrollwheel: false,
    mapTypeControlOptions: {
      style:google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
      position: google.maps.ControlPosition.RIGHT_BOTTOM
    }
  };

  mapInfo.map = new google.maps.Map(document.getElementById("content-map"), mapOptions);
  casellaMap = mapInfo.map;

  google.maps.event.trigger(casellaMap, "resize");

  nameArg = location.search.match(/(?:\?|&)name=([^&]+)(?:&|$)/);
  nameArg = null != nameArg && nameArg.length ? nameArg[1] : 'all';

  jQuery.ajax({
    url: "/locationMarkers/" + nameArg + "?_format=json",
    method: "GET",
    headers: {
      "Content-Type": "application/json"
    },
  }).done(function (data) {
    setCenter(data, mapInfo);
    initMarkers(data, mapInfo);
    jQuery('.locationMap-key-location-container input').on('change', {mapInfo: mapInfo}, filterChangeHandler);
    jQuery('.locationMap-listView-indListing-content-viewMap-btn.panelControlBtn').on('click', {mapInfo: mapInfo}, findLocationClickHandler);
  }).error(function (data) {
    // console.log('error', data);
  });

  bindLinkContainerToggle();
}

function bindLinkContainerToggle() {
  jQuery('.locationMap-link-accordion-container').on('click', function(event){
    if (event.target != event.currentTarget) {
      return;
    }

    var accordion = jQuery(event.currentTarget),
        accordionToggleIcon = accordion.children('.locationMap-marker-toggle-icon');

    accordion.children('.locationMap-marker-accordion-toggle').removeClass('open');

    accordion.toggleClass("open");
    handleMapResize();

    if (accordion.hasClass("open")) {
      accordionToggleIcon.html("<i class='fa fa-minus-circle'></i>");
    } else {
      accordionToggleIcon.html("<i class='fa fa-plus-circle'></i>");
    }
  });
}

/**
 * Check if we have a town seach. If we do then add a marker and center/zoom in on it.
 *
 * @param markersInfo
 * @param mapInfo
 */
function setCenter(markersInfo, mapInfo) {
  if ("undefined" == typeof markersInfo.townInfo || "undefined" == typeof markersInfo.townInfo.latitude) {
    return;
  }

  var marker, markerCenter = new google.maps.LatLng(markersInfo.townInfo.latitude, markersInfo.townInfo.longitude);
  casellaMapCenter = markerCenter;

  mapInfo.map.setZoom(10);

  marker = new google.maps.Marker({
    position: markerCenter,
    map: mapInfo.map,
    title: markersInfo.townInfo.townName,
    icon: '//maps.google.com/mapfiles/ms/icons/blue-dot.png'
  });

  setTimeout(function(){google.maps.event.trigger(casellaMap, "resize");casellaMap.panTo(casellaMapCenter);},1500);

}

/**
 * Handle the addition of all the markers.
 *
 * @param markersInfo
 * @param mapInfo
 */
function initMarkers(markersInfo, mapInfo) {
  var marker, clustererOptions, markerClusterer;

  // Short circuit if we have no markers.
  if ("undefined" == typeof markersInfo.markers || !markersInfo.markers.length) {
    return;
  }

  // Create the marker and info window objects.
  markersInfo.markers.forEach(function(markerInfo, index) {
    initMarker(markerInfo, mapInfo, index);
  });

  // Add the markers to the map. The markers are handled by the clusterer, so set that up first.
  // The markers come in pairs, so the minimum size for a cluster is 3 markers, or 2 actual locations.
  var clustererOptions = {
    imagePath: '/themes/custom/casella/i/clusters/cluster',
    maxZoom: 8,
    gridSize: 100,
    styles: [
      {height: 100, width: 100, url: '/themes/custom/casella/i/clusters/cluster1.png', textSize: 20, textColor: '#FFFFFF'},
      {height: 150, width: 150, url: '/themes/custom/casella/i/clusters/cluster2.png', textSize: 20, textColor: '#FFFFFF'},
      {height: 200, width: 200, url: '/themes/custom/casella/i/clusters/cluster3.png', textSize: 20, textColor: '#FFFFFF'},
    ],
    zoomOnClick: true,
    minimumClusterSize: 3
  };

  mapInfo.clusterer = new MarkerClusterer(mapInfo.map, mapInfo.markers.all, clustererOptions);
  mapInfo.clusterer.setCalculator(newCalculator);
}

/**
 * Create a new marker.
 *
 * @param markerInfo
 * @param mapInfo
 * @param index
 */
function initMarker(markerInfo, mapInfo, index) {
  var anchor = new google.maps.LatLng(markerInfo.latitude, markerInfo.longitude), background, marker, infoWindow, baseIndex = (index - 1) * 2;

  // Create the background first.
  background = new google.maps.Marker({
    position: anchor,
    // map: mapInfo.map,
    icon: mapInfo.iconPaths.background,
    zIndex: baseIndex
  });

  // Create the marker.
  // Dont designate the map, the marker clusterer library does that.
  marker = new google.maps.Marker({
    position: anchor,
    // map: mapInfo.map,
    title: markerInfo.title,
    icon: mapInfo.iconPaths[markerInfo.typeLC],
    zIndex: baseIndex + 1
  });

  // Add the marker to the global holder.
  if ("undefined" == typeof mapInfo.markers[markerInfo.typeLC]) {
    mapInfo.markers[markerInfo.typeLC] = []
  }
  mapInfo.markers[markerInfo.typeLC].push(marker);
  mapInfo.markers[markerInfo.typeLC].push(background);
  mapInfo.markers.all.push(marker);
  mapInfo.markers.all.push(background);
  mapInfo.markers.allIndexed['marker' + markerInfo.nid] = marker;

  // Extend the bounds.

  // Add an info window.
  infoWindow = new google.maps.InfoWindow({
    content: buildInfoWindowContent(markerInfo)
  });

  mapInfo.infoWindows['info' + markerInfo.nid] = infoWindow;

  google.maps.event.addListener(marker, "click", function () {
    clearInfoWindows(mapInfo.infoWindows);
    infoWindow.open(mapInfo.map, marker);
    casellaMap.panTo(marker.getPosition());
  });
  google.maps.event.addListener(background, "click", function () {
    clearInfoWindows(mapInfo.infoWindows);
    infoWindow.open(mapInfo.map, marker);
  });
}

/**
 * Takes in an array of marker info and returns formatted html.
 *
 * @param markerInfo
 * @returns {string}
 */
function buildInfoWindowContent(markerInfo) {
  return '<div class="markerWrapper">' +
    '<h3 class="markerTitle">' + markerInfo.title + '</h3>' +
    '<div class="markerType">' + markerInfo.type + '</div>' +
    '<div class="markerLink"><a href="' + markerInfo.path + '">Read More</a></div>' +
    '</div>';
}

/**
 * Closes all passed info windows.
 *
 * @param windows
 */
function clearInfoWindows(windows) {
  var window;
  for (window in windows) {
    if (windows.hasOwnProperty(window)) {
      windows[window].close();
    }
  }
}

/**
 * Returns an object containing all the marker svg paths.
 *
 * @returns {object}
 */
function getIconPaths() {
  var origin = new google.maps.Point(20, 48);

  var iconInfo =  {
    dropoff: {
      path: "M49.6,25.1c0-13.5-11-24.5-24.5-24.5S0.6,11.5,0.6,25.1c0,6.7,2.7,12.8,7,17.2h0l0.1,0.1c0,0,0,0,0.1,0.1l17.3,17.2 l17.3-17.2c0,0,0,0,0.1-0.1l0.1-0.1h0C46.9,37.8,49.6,31.8,49.6,25.1z M25.1,44.5c-9.7,0-17.7-7.1-19.2-16.3 c-0.1-0.4-0.1-0.8-0.2-1.1c-0.1-0.7-0.1-1.3-0.1-2c0-10.7,8.7-19.4,19.4-19.4s19.4,8.7,19.4,19.4c0,0.7,0,1.3-0.1,2 c0,0.4-0.1,0.8-0.2,1.1C42.8,37.4,34.8,44.5,25.1,44.5z M36.2,28.7c0-0.6,0.5-1.2,1.1-1.2h0.5c0.6,0,1.1-0.5,1.1-1.2v-2.2 c0-0.6-0.5-1.2-1.1-1.2h-9.5c-0.6,0-1.4,0.4-1.9,0.8l-0.6,0.7c-0.4,0.5-1.1,0.5-1.5,0l-0.6-0.7c-0.4-0.5-1.3-0.8-1.9-0.8h-9.5 c-0.6,0-1.1,0.5-1.1,1.2v2.2c0,0.6,0.5,1.2,1.1,1.2h0.5c0.6,0,1.1,0.5,1.1,1.2v9.4c3,2.6,6.9,4.2,11.2,4.2c4.3,0,8.2-1.6,11.2-4.2 V28.7z M20,35.6c0,0.6-0.5,1.2-1.1,1.2c-0.6,0-1.1-0.5-1.1-1.2v-5.7c0-0.6,0.5-1.2,1.1-1.2c0.6,0,1.1,0.5,1.1,1.2V35.6z M26.1,35.6 c0,0.6-0.5,1.2-1.1,1.2c-0.6,0-1.1-0.5-1.1-1.2v-5.7c0-0.6,0.5-1.2,1.1-1.2c0.6,0,1.1,0.5,1.1,1.2V35.6z M31.2,36.7 c-0.6,0-1.1-0.5-1.1-1.2v-5.7c0-0.6,0.5-1.2,1.1-1.2c0.6,0,1.1,0.5,1.1,1.2v5.7C32.2,36.2,31.8,36.7,31.2,36.7z M25,7.9 c0.5,0,0.9,0,1.4,0.1v7.8c0,0.6,0.3,0.7,0.7,0.3l2.2-2.4c0.4-0.4,1.1-0.5,1.5-0.1l0.4,0.4c0.4,0.4,0.5,1.1,0.1,1.5l-5.5,6 c-0.4,0.4-1.1,0.4-1.5,0l-5.5-6c-0.4-0.4-0.4-1.1,0.1-1.5l0.4-0.4c0.4-0.4,1.1-0.4,1.5,0.1l2.2,2.4c0.4,0.4,0.7,0.3,0.7-0.3V7.9 C24.1,7.9,24.6,7.9,25,7.9z",
      scale: 1,
      fillColor: '#939598',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    hauling: {
      path: "M39.7,19.8C39.7,9,30.9,0.2,20,0.2C9.1,0.2,0.3,9,0.3,19.8c0,5.4,2.2,10.3,5.7,13.8h0L6,33.7c0,0,0,0,0,0L20,47.6l13.9-13.9 c0,0,0,0,0,0l0.1-0.1h0C37.5,30.1,39.7,25.2,39.7,19.8z M20,35.5c-8.6,0-15.6-7-15.6-15.6S11.3,4.2,20,4.2s15.6,7,15.6,15.6 S28.6,35.5,20,35.5z M20.5,26.3c0-1.5-1.2-2.8-2.8-2.8c-1.5,0-2.8,1.2-2.8,2.8h-0.6c0-1.5-1.2-2.8-2.8-2.8c-1.5,0-2.8,1.2-2.8,2.8 H7.8c0,0-1.6-2.8-1.6-6.4c0-6.1,4.5-8.7,4.5-8.7h13.8v15.1H20.5z M30.9,26.3c0,1.2-1,2.3-2.3,2.3s-2.3-1-2.3-2.3 c0-1.2,1-2.3,2.3-2.3S30.9,25,30.9,26.3z M33.1,26.3v-6.4l-2.1-4.3h-6v0v10.7h1c0-1.5,1.2-2.8,2.8-2.8c1.5,0,2.8,1.2,2.8,2.8H33.1z M26.4,19.8v-3.4h3.8l1.6,3.4H26.4z M17.7,28.5c-1.2,0-2.3-1-2.3-2.3c0-1.2,1-2.3,2.3-2.3c1.2,0,2.3,1,2.3,2.3 C20,27.5,18.9,28.5,17.7,28.5z M11.6,28.5c-1.2,0-2.3-1-2.3-2.3c0-1.2,1-2.3,2.3-2.3c1.2,0,2.3,1,2.3,2.3 C13.9,27.5,12.8,28.5,11.6,28.5z",
      scale: 1,
      fillColor: '#00253f',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    landfill: {
      path: "M33.9,33.8C33.9,33.8,33.9,33.8,33.9,33.8l0.1-0.1h0c3.5-3.6,5.7-8.4,5.7-13.8C39.7,9,30.9,0.2,20,0.2S0.3,9,0.3,19.8 c0,5.4,2.2,10.3,5.7,13.8h0L6,33.7c0,0,0,0,0,0L20,47.6L33.9,33.8z M4.3,19.8c0-8.6,7-15.6,15.6-15.6s15.6,7,15.6,15.6 c0,8.6-7,15.6-15.6,15.6S4.3,28.5,4.3,19.8z M19.7,33.6c-4.5,0-8.5-2.2-11.1-5.5c8.6-1.5,16.1-4.3,21.6-7.9 c-0.2-0.1-0.3-0.2-0.5-0.3c-0.8-0.5-1.6-1.1-2.4-1.8c-0.2-0.2-0.3-0.2-0.5-0.2c-0.4,0-0.9,0.2-1.4,0.5l-0.2,0.1 c-0.7,0.3-1.3,0.6-2,1c-1.7,0.9-3.4,1.8-5.3,1.8c-0.8,0-1.6-0.2-2.3-0.5l-0.5-0.2c-1.5-0.6-2.8-1.3-4.2-1.3c-0.2,0-0.4,0-0.6,0 c-1.2,0.2-1.9,1-3,2.5c-0.3,0.5-0.7,0.9-1,1.3c-0.1-0.5-0.2-1-0.3-1.5c0.1-0.2,0.3-0.3,0.4-0.5c1.1-1.7,2.1-2.8,3.8-3 c2-0.3,3.9,0.7,5.9,1.5c3.1,1.4,5.9-1.1,8.8-2.4c1.1-0.5,2.3-1.1,3.3-0.1c1.2,1.1,2.6,1.8,3.8,2.8c0.5,0.4,1,0.8,1.5,1.2 C32.8,28.1,26.9,33.6,19.7,33.6z",
      scale: 1,
      fillColor: '#e46425',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    organics: {
      path: "M39.7,19.9C39.7,9,30.9,0.2,20,0.2C9.1,0.2,0.3,9,0.3,19.9c0,5.4,2.2,10.3,5.7,13.8h0L6,33.8c0,0,0,0,0,0L20,47.7l13.9-13.9 c0,0,0,0,0,0l0.1-0.1h0C37.5,30.1,39.7,25.2,39.7,19.9z M20,35.5c-8.6,0-15.6-7-15.6-15.6c0-8.6,7-15.6,15.6-15.6s15.6,7,15.6,15.6 C35.6,28.5,28.6,35.5,20,35.5z M20,33.7c-0.8,0-1.6-0.1-2.3-0.2c0.1-0.3,0.8-1.9,1.1-2c0.3-0.1,1.3,0.8,1.3,0.8s-0.5-1.5-0.8-1.6 c-0.3-0.1,1.2-1.9,1.4-2.1c0.2-0.1,1.5,1.3,1.6,1.5c0.1,0.2-0.7-1.8-1.1-2.1l1.3-1.7c0,0,1.5,1.3,1.9,1.8c0.4,0.5-0.6-1.6-1.4-2.4 l1.4-1.4c0,0,2.1,1.2,4,5c0.2,0.3,0.3,0.7,0.4,1C26.4,32.5,23.4,33.7,20,33.7z M14.8,32.7c0.6-1,1.5-2.3,2.7-3.6L17.3,29 c0,0,0.8-1.6,1.3-1.8c0.2-0.1,0.4,0,0.6,0.1c0.1-0.1,0.3-0.2,0.4-0.4l-0.4-0.3c0,0,0.8-1.4,1.3-1.5c0.3-0.1,0.6,0.1,0.9,0.2 c0.1-0.1,0.3-0.2,0.4-0.3L21,24.7c0,0,1-1.5,1.5-1.6c0.4-0.1,0.8,0.2,1,0.4c4.9-3.7,7.4-4.4,7.4-4.4s-7.3,3.8-11.7,10.4 c-0.9,1.4-1.7,2.6-2.3,3.9C16.2,33.2,15.4,32.9,14.8,32.7z M12.5,31.5c0.3-0.4,0.6-0.9,1-1.3l-0.1,0c0,0,0.8-1.4,1-1.5 c0.1,0,0.1,0,0.2,0c0.2-0.3,0.4-0.5,0.7-0.8l-0.2-0.1c0,0,0.7-1.4,1.1-1.5c0.1-0.1,0.4,0,0.6,0c0.2-0.2,0.4-0.4,0.5-0.6l-0.4-0.2 c0,0,0.7-1.4,1.1-1.5c0.2-0.1,0.6,0,0.9,0.1c0.2-0.2,0.4-0.3,0.6-0.5l-0.6-0.2c0,0,0.6-1.2,1.3-1.5c0.5-0.2,0.9,0.1,1.1,0.3 c6.3-5,10.2-6.2,10.2-6.2s-9.1,5.3-12.3,9.3c-2,2.5-3.6,5.4-4.5,7.3C13.9,32.3,13.2,31.9,12.5,31.5z M23.1,12c0,0-0.4-2.2,1.3-3.4 C24.4,8.5,25.3,10.6,23.1,12z M17.4,18c0,0-0.8-2.1,0.7-3.6C18,14.4,19.3,16.3,17.4,18z M19.1,15.7c0,0-0.7-2.1,0.9-3.5 C20,12.1,21.2,14.1,19.1,15.7z M10.5,29.9c0.2-0.2,0.3-0.4,0.5-0.6l-0.2-0.1c0,0,0.7-1.4,0.8-1.4c0.1,0,0.2,0,0.4,0.1 c0.2-0.3,0.4-0.5,0.6-0.7l-0.3-0.2c0,0,0.4-1.4,0.9-1.6c0.2-0.1,0.5,0,0.7,0.2c0.2-0.3,0.5-0.5,0.7-0.8c-0.3-0.1-0.6-0.3-0.7-0.3 c-0.2,0,0.4-1,0.6-1.1c0.2-0.1,0.9,0,1.3,0.1c0.2-0.2,0.5-0.5,0.8-0.8c-0.5-0.2-1-0.4-1.2-0.4c-0.3,0.1,0.6-1.3,0.8-1.4 c0.2-0.1,1.3,0.1,1.9,0.3c2.8-2.5,7-5.7,12.7-7.8c0,0-6.9,3.9-11.4,8.3c-3.9,3.8-6.2,7.3-7.4,9.6C11.6,30.9,11,30.4,10.5,29.9z M19.7,16.3c0,0,1.6-1.5,3.6-0.7C23.3,15.6,22,17.4,19.7,16.3z M21,13.9c0,0-0.6-2.1,1.1-3.5C22.1,10.4,23.1,12.4,21,13.9z M17.9,18.3c0,0,1.5-1.6,3.5-0.9C21.4,17.3,20.3,19.3,17.9,18.3z M27.8,8.5C27.8,8.5,27.9,8.5,27.8,8.5c0,0.2-0.3,2.3-2.7,2.4 C25.1,10.9,25.7,8.8,27.8,8.5z M21.8,14.4c0,0,1.6-1.5,3.6-0.7C25.4,13.7,24.2,15.5,21.8,14.4z M23.7,12.5c0,0,1.7-1.4,3.6-0.4 C27.3,12,25.9,13.8,23.7,12.5z M13,24.4c-0.5,0.8-1.9,3.1-2.9,5.1c-0.6-0.6-1.1-1.2-1.5-1.8c0.2,0,0.4,0,0.6,0 c0.2-0.3,0.4-0.7,0.7-1c-0.5-0.1-1.2-0.2-2.1-0.3c-0.2-0.3-0.3-0.6-0.4-0.9c0.2,0,0.3-0.1,0.5-0.1c1.4-0.2,2.4-0.2,3-0.2 c0.3-0.4,0.5-0.7,0.8-1.1c-0.6-0.2-1.7-0.5-3.4-0.3c-0.5,0.1-1,0.1-1.5,0.2c-0.1-0.2-0.1-0.5-0.2-0.7C7.4,23.2,8.3,23,9,22.9 c1.8-0.2,3,0,3.6,0.1c0.3-0.4,0.7-0.8,1-1.2c-0.8-0.1-3-0.4-5.1-0.1c-0.7,0.1-1.5,0.2-2.2,0.3c0-0.2-0.1-0.5-0.1-0.7 c1.3-0.4,2.7-0.7,4.3-0.8c2.6-0.2,3.8-0.1,4.3,0c0.8-0.8,1.7-1.5,2.4-2C17.2,18.6,13.9,23,13,24.4z",
      scale: 1,
      fillColor: '#3d963a',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    recycling: {
      path: "M39.7,19.9C39.7,9,30.9,0.2,20,0.2S0.3,9,0.3,19.9c0,5.4,2.2,10.3,5.7,13.8h0L6,33.8c0,0,0,0,0,0L20,47.7l13.9-13.9 c0,0,0,0,0,0l0.1-0.1h0C37.5,30.1,39.7,25.2,39.7,19.9z M20,35.5C20,35.5,20,35.5,20,35.5L20,35.5c-8.6,0-15.6-7-15.6-15.6 S11.3,4.2,20,4.2h0c0,0,0,0,0,0c8.6,0,15.6,7,15.6,15.6S28.6,35.5,20,35.5z M17,28.1l3,2.9c-0.4,0-1.1,0-1.9,0c-1.3,0-2.5,0-3.8,0 c-1.3,0-2.3-0.6-3-1.6c-0.1-0.1-0.1-0.2-0.2-0.3c-1.3-2.1-3.5-5.9-3.9-6.5c-0.6-1.1-0.3-2.2,0.1-3.2c0.6-1.3,0.9-1.8,0.9-1.8 l0.1-0.2l-0.2-0.1l-1.5-0.8l6.9-2l2.1,6.9l-1.5-0.7l-0.2-0.1l-0.1,0.2c0,0-0.7,1.4-2.1,4.1c3,0.1,7,0.2,8.2,0.2L17,27.7l-0.2,0.2 L17,28.1z M32.4,23c-1.2,2.1-3.3,5.8-3.7,6.5c-0.6,1.1-1.8,1.4-2.8,1.5c-1.4,0.1-2,0.1-2,0.1l-0.3,0l0,0.3l0,1.7l-5.2-5l5-5.2 l0.1,1.6l0,0.3l0.3,0c0,0,0.4,0,4.6-0.2c-1.4-2.6-3.1-5.9-3.7-7.1l3.8,1l0.3,0.1l0.1-0.3l1.1-4.1c0.5,0.9,1.5,2.7,2.8,5 c0.6,1.1,0.7,2.3,0,3.4C32.6,22.7,32.5,22.8,32.4,23z M17.9,7.5c2.4,0,5.3,0,6.1,0h0c1.2,0,2.1,0.9,2.7,1.7c0.8,1.2,1.1,1.7,1.1,1.7 l0.1,0.2l0.2-0.1l1.5-0.9l-1.8,7l-7-1.7l1.4-0.9l0.2-0.1l-0.1-0.2c0,0-0.8-1.3-2.4-3.9c0,0,0,0,0,0c-1.6,2.5-3.5,5.6-4.2,6.7 l-1.1-3.7l-0.1-0.3L14.2,13l-4.1,1.1c0.5-0.9,1.6-2.7,2.9-5c0.7-1.1,1.7-1.7,2.9-1.7h0C16.1,7.5,17.7,7.5,17.9,7.5 C17.9,7.5,17.9,7.5,17.9,7.5z",
      scale: 1,
      fillColor: '#0082ca',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    transfer: {
      path: "M39.7,19.8C39.7,9,30.9,0.2,20,0.2S0.3,9,0.3,19.8c0,5.4,2.2,10.3,5.7,13.8h0L6,33.7c0,0,0,0,0,0L20,47.6l13.9-13.9 c0,0,0,0,0,0l0.1-0.1h0C37.5,30.1,39.7,25.2,39.7,19.8z M20,35.5c-7.8,0-14.2-5.7-15.4-13.1c0-0.3-0.1-0.6-0.1-0.9 c-0.1-0.5-0.1-1.1-0.1-1.6c0-8.6,7-15.6,15.6-15.6s15.6,7,15.6,15.6c0,0.5,0,1.1-0.1,1.6c0,0.3-0.1,0.6-0.1,0.9 C34.2,29.8,27.8,35.5,20,35.5z M25.6,11.8l-2-2.3c-0.4-0.4-0.2-1.1,0.3-1.6l0.5-0.4c0.5-0.5,1.2-0.5,1.6-0.1l4.9,5.7 c0.4,0.4,0.2,1.1-0.3,1.5l-7.4,5.7c-0.5,0.4-1.3,0.4-1.6-0.1L21.3,20c-0.3-0.5-0.1-1.2,0.4-1.6l2.9-2.3c0.5-0.4,0.5-0.8-0.1-0.8 H13.2c-0.6,0-1-0.5-0.9-1.1l0.1-0.5c0.1-0.6,0.7-1.1,1.4-1.1h11.3C25.7,12.6,25.9,12.2,25.6,11.8z M14.7,26.9l2,2.3 c0.4,0.4,0.2,1.1-0.3,1.6l-0.5,0.4c-0.5,0.5-1.2,0.5-1.6,0.1l-4.9-5.7c-0.4-0.4-0.2-1.1,0.3-1.5l7.4-5.7c0.5-0.4,1.3-0.4,1.6,0.1 l0.3,0.4c0.3,0.5,0.1,1.2-0.4,1.6l-2.9,2.3c-0.5,0.4-0.5,0.8,0.1,0.8h11.3c0.6,0,1,0.5,0.9,1.1L27.9,25c-0.1,0.6-0.7,1.1-1.4,1.1 H15.2C14.6,26.1,14.3,26.5,14.7,26.9z",
      scale: 1,
      fillColor: '#a154a1',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    corporate: {
      path: "M39.7,19.8C39.7,9,30.9,0.2,20,0.2C9.1,0.2,0.3,9,0.3,19.8c0,5.4,2.2,10.3,5.7,13.8h0L6,33.7c0,0,0,0,0,0L20,47.6l13.9-13.9 c0,0,0,0,0,0l0.1-0.1h0C37.5,30.1,39.7,25.2,39.7,19.8z M20,35.5c-8.6,0-15.6-7-15.6-15.6S11.3,4.2,20,4.2s15.6,7,15.6,15.6 S28.6,35.5,20,35.5z M20,33.7c3.8,0,7.2-1.5,9.7-4V11.6h1.1v-0.2L20.3,6c-0.1,0-0.2,0-0.3,0c-0.1,0-0.2,0-0.3,0L9.2,11.3v0.2h1.1 v18.1C12.8,32.1,16.2,33.7,20,33.7z M24.4,14.1h3v5.6h-3V14.1z M18.5,14.1h3v5.6h-3V14.1z M18,23.6h4v6.2h-4V23.6z M12.6,14.1h3v5.6 h-3C12.6,19.7,12.6,14.1,12.6,14.1z",
      scale: 1,
      fillColor: '#2f559a',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    },
    background: {
      path: "M39.7,19.8C39.7,9,30.9,0.2,20,0.2C9.1,0.2,0.3,9,0.3,19.8c0,5.4,2.2,10.3,5.7,13.8h0L6,33.7c0,0,0,0,0,0L20,47.6l13.9-13.9 c0,0,0,0,0,0l0.1-0.1h0C37.5,30.1,39.7,25.2,39.7,19.8z",
      scale: 1,
      fillColor: '#FFFFFF',
      fillOpacity: 1,
      strokeOpacity: 0,
      anchor: origin
    }
  };
  iconInfo.office = iconInfo.corporate;

  return iconInfo;
}

function newCalculator(markers, numStyles) {
  var index = 0;
  var count = markers.length / 2;
  var dv = count;
  while (dv !== 0) {
    dv = parseInt(dv / 10, 10);
    index++;
  }

  index = Math.min(index, numStyles);
  return {
    text: count,
    index: index
  };
}

function filterChangeHandler(event) {
  var mapInfo = event.data.mapInfo,
      $target = jQuery(event.currentTarget),
      targetFor = $target.attr('name');

  if ("undefined" == typeof(mapInfo.markers[targetFor])) {
    return;
  }

  if ($target[0].checked) {
    // Add all the markers.
    mapInfo.markers[targetFor].forEach(function(marker, index){
      mapInfo.clusterer.addMarker(marker);
    });
    jQuery('.locationMap-listView-indListing-container.' + targetFor).show();

    return;
  }

  // Remove all the markers.
  mapInfo.markers[targetFor].forEach(function(marker, index){
    mapInfo.clusterer.removeMarker(marker);
  });
  jQuery('.locationMap-listView-indListing-container.' + targetFor).hide();
}

function findLocationClickHandler(event) {
  var mapInfo = event.data.mapInfo,
    $target = jQuery(event.currentTarget),
    guid = $target.attr('data-guid');

  //mapInfo.map.setCenter(mapInfo.markers.allIndexed['marker' + guid].position);
  mapInfo.map.setZoom(10);
  clearInfoWindows(mapInfo.infoWindows);
  mapInfo.infoWindows['info' + guid].open(mapInfo.map, mapInfo.markers.allIndexed['marker' + guid]);

  // Set the global and force the re center
  casellaMapCenter = mapInfo.markers.allIndexed['marker' + guid].position;
  casellaMap.setCenter(casellaMapCenter);
  // And scroll up in case we are too far down
  jQuery('body,html').animate({ 'scrollTop': jQuery('.panelControlBtn[data-rel="#content-map"]').offset().top - 100}, 200);

  jQuery('.panelControlBtn[data-rel="#content-map"]').click();


}

function debounce(func, wait, immediate) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
};

function handleMapResize() {
  jQuery.fn.matchHeight._update();
  google.maps.event.trigger(casellaMap, "resize");
}

jQuery(document).ready(function() {
    jQuery(function() {
        jQuery('.equalize').matchHeight({
            byRow: true,
            property: 'height',
        });
    });
});
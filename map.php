<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>DR2ooo Map Constructor</title>
    <style type="text/css">
    v\:* {
      behavior:url(#default#VML);
    }
    </style>
    <script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key=ABQIAAAACA_BeDOGLjGEMv3Yj7kBixRYnpSX3XH2GzgcN4Kr-HSk7kZTFhT1ms6WYdPtezNFPOcyew6VGrY0Tw"
      type="text/javascript"></script>
    <script type="text/javascript">
//<![CDATA[

function Build() { }
Build.prototype = new GControl();
Build.prototype.initialize = function(map) {
  var build = document.createElement("div");
  map.getContainer().appendChild(build);
  return build;
}
Build.prototype.getDefaultPosition = function() {
  return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 7));
}



var baseIcon = new GIcon();
baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
baseIcon.iconSize = new GSize(20, 34);
baseIcon.shadowSize = new GSize(37, 34);
baseIcon.iconAnchor = new GPoint(9, 34);

// Creates a marker whose info window displays the letter corresponding
// to the given index.
function createMarker(point, index) {
  // Create a lettered icon for this point using our icon class
  var icon = new GIcon(baseIcon);
  icon.image = "http://www.megatrend.com/images/markermt.png";
  var marker = new GMarker(point, icon);
  return marker;
}

function load() {
  if (GBrowserIsCompatible()) {
    var map = new GMap2(document.getElementById("map"));
    map.setCenter(new GLatLng(45.792484,16.034546), 14);
    map.setMapType(G_NORMAL_MAP);
    map.addControl(new Build());
 var point = new GLatLng(45.792484,16.034546);
 map.addOverlay(createMarker(point, 0));
 

  }
}

//]]>
    </script>

    <style type="text/css">
      body {
        margin:0;
        border:0;
        padding:0;
      }
      div {
        margin:0;
        border:0;
        padding:0;
      }
    </style>
  </head>
  <body onload="load()" onunload="GUnload()">
    <div id="map" style="width: 400px; height: 266px"></div>
  </body>
</html>
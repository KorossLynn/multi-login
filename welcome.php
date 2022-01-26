<?php

include('functions.php');
if (!isLoggedIn()) {
	$_SESSION['msg'] = "You must log in first";
	header('location: login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" ></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" ></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" ></script>
	<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script> <style>
	<style>
	#map { position: absolute; top:0; bottom: 0; left: 0; right: 0; }
	html, body {
          margin: 0px;
          padding: 0px;
      }
      #map {
        position :absolute;
        width: 100%;
		    height: 100%;
        top: 0;
        bottom: 0;
      }

      .controls{
        position: relative;
        top: 100px;
        z-index: 99999;
        margin: 2%;
        width: 30%;
        overflow-x: scroll;
				/* box-shadow: 2px 2px; */
				border-radius: 16px;
        background-color: #ffff;
				padding: 20px;
      }

      .display{
        min-height: 300px;
        max-height: 300px;
        overflow-y: scroll;
        padding: 2%;
      }

	</style>
	</head>
	<body>
     <div id="map"></div>
	 <div class="controls">
	 <!-- <h2> Nairobi status upfile_upload</h2> -->
	 <p>Welcome,	<strong><?php echo $_SESSION['user']['username']; ?></strong></p>
	 <div>Filter By  year <select data-con-yr>
    	         <option value="January 2021">January 2021</option>
				 <option value="February 2021">February 2021</option>
				 <option value="March 2021">March 2021</option>
				 <option value="April 2021">April 2021</option>
				 <option value="May 2021">May 2021</option>
				 <option value="June 2021">June 2021</option>
				 <option value="July 2021">July 2021</option>
				 <option value="August 2021">August 2021</option>
				 <option value="September 2021">September 2021</option>
				 <option value="October 2021">October 2021</option>
				 <option value="November 2021">November 2021</option>
				 <option value="December 2021">December 2021</option>
    </select></div>
	&nbsp;&nbsp;<span data-fy-span></span><br>
    <div>View Road by Status <select data-prj-status></select> </div>&nbsp;&nbsp;<span data-status-span></span><br>
    <div>View Road by Remarks <select data-prj-remarks></select> </div>&nbsp;&nbsp;<span data-remarks-span></span><br>
    <!--input type="file" id="df_upload" value="Upload file"><hr-->
		<label for="">Select File to View Details</label>
		<input type="file" id="df_upload" class="form-control" value="Select Project File"><br>
  <a href="index.php?logout='1'" class="btn btn-danger">logout here</a>
   <div class="display">
        <h3>Click on a Marker on the map to view its details here</h3>
        <div id="prop-details"></div>
      </div>
</div>
	 <script>



	 var map = L.map('map').setView([-1.27, 36.81],11);

   let express_dfs = null;
  let express_df = null;
  let feature_props_status = [];
  let feature_props_remarks = [];
  let expressGeojsonLayer = L.featureGroup([]);
  let expressGeojson;
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  var marker = L.marker([-1.426953,36.959130]).addTo(map);
	 var marker = L.marker([-1.260106,36.771317]).addTo(map);
	 L.marker([-1.426953,36.959130]).addTo(map)
    .bindPopup('Nairobi statusway start of construction zone')
    .openPopup();
	L.marker([-1.260106,36.771317]).addTo(map)
    .bindPopup('Nairobi statusway end of construction zone')
    .openPopup()

  $(document).ready(function(e){
    $("#df_upload").on('change', function(){
      const input = document.getElementById("df_upload").files[0];
      const reader = new FileReader();
      $("[data-con-yr]").on('change', function() {
      	console.log($(this).val());
      	express_dfs = JSON.parse(JSON.stringify(express_df));
      	express_dfs.features = express_dfs.features.filter(df => df.properties['date'] === $(this).val());
        $("[data-fy-span]").text(express_dfs.features.length);
        $("[data-remarks-span]").text('');
        $("[data-status-span]").text('');
		    loadData(express_dfs);
      	console.log(express_dfs);
        console.log(express_df);
      });

      $("[data-prj-status]").on('change', function() {
        console.log($(this).val());
        express_dfs = JSON.parse(JSON.stringify(express_df));
        express_dfs.features = express_dfs.features.filter(df => df.properties['status'] === $(this).val());
        $("[data-status-span]").text(express_dfs.features.length);
        $("[data-fy-span]").text('');
        $("[data-remarks-span]").text('');
        loadData(express_dfs);
        console.log(express_dfs);
        console.log(express_df);
      });


      $("[data-prj-remarks]").on('change', function() {
        console.log($(this).val());
        express_dfs = JSON.parse(JSON.stringify(express_df));
        express_dfs.features = express_dfs.features.filter(df => df.properties['remarks'] === $(this).val());
        $("[data-remarks-span]").text(express_dfs.features.length);
        $("[data-status-span]").text('');
        $("[data-fy-span]").text('');
        loadData(express_dfs);
        console.log(express_dfs);
        console.log(express_df);
      });

      reader.onload = function(){
        express_df = JSON.parse(reader.result);
        express_dfs = JSON.parse(reader.result);
        console.log(express_df);

        loadData(express_df);
        loadAttr();
      }
      reader.readAsText(input);
    });
  });

  function loadData(data) {
    try{
      expressGeojsonLayer.removeLayer(expressGeojson);
      map.removeLayer(expressGeojsonLayer);

    } catch (err) {
      console.log("loading for the first time");
    } finally {
      expressGeojson = L.geoJSON(data,{
          pointToLayer: function (feature, latlng) {
              return L.circleMarker(latlng, geojsonMarkerOptions(feature));
          },
          onEachFeature:onEachFeature
        });
      expressGeojsonLayer.addLayer(expressGeojson);
      expressGeojsonLayer.addTo(map);
      map.fitBounds(expressGeojsonLayer.getBounds());
    }
  }

  function loadAttr() {
    feature_props_status = Array.from(new Set(feature_props_status));
    feature_props_remarks = Array.from(new Set(feature_props_remarks));
    $("[data-prj-status]").html('');
    $("[data-prj-remarks]").html('');
    feature_props_status.map(val => {
      $("[data-prj-status]").append($("<option>", {'value':val, 'text':val}));
    });

    feature_props_remarks.map(val => {
      $("[data-prj-remarks]").append($("<option>", {'value':val, 'text':val}));
    });
  }

  var geojsonMarkerOptions = function(feature) {
    let red = "#E94D4D";
    let orange = "#ff7800";
    let green = "#0A7F07";
    let colr = ''

    props = feature.properties;
    /*$opt_year = $("<option>", {'value':props['date'], 'text':props['date']});
    $("[data-con-yr]").append($opt_year);*/
    switch(props.remarks){
      case "Diversion in use" : {
        colr = green;
        return {
      radius: 8,
      fillColor: colr,
      color: colr,
      weight: 1,
      opacity: 1,
      fillOpacity: 0.8
    }
      };
      case "Diversion partially closed" : {
        colr = orange;
        return {
      radius: 8,
      fillColor: colr,
      color: colr,
      weight: 1,
      opacity: 1,
      fillOpacity: 0.8
    }
      };
      case "Diversion Closed" : {
        colr = red;
        return {
      radius: 8,
      fillColor: colr,
      color: colr,
      weight: 1,
      opacity: 1,
      fillOpacity: 0.8
    }
      }
    }

};

  function displayProperties(properties){
    $header = $("<h4>"+properties["project_name"]+"</h4>");
    Object.keys(properties).map(key=>{
      $prop = $("<h6>"+key+"</h6>", {'class':'property-name'})
        .append($("<p>"+properties[key]+"</p>", {'class':'property-value'})).append($("<br>"));
      $header.append($prop);
    });
    $("#prop-details").html($header);
  }

  function onEachFeature(feature, layer) {
    // does this feature have a property named popupContent?
    if (feature.properties !== null) {
        let prop = feature.properties;
		console.log(feature);
        feature.properties.status = prop.status.toLowerCase();
        feature.properties.remarks = prop.remarks ? prop.remarks.toLowerCase() : "";
        feature_props_status.push(prop.status.toLowerCase());
        feature_props_remarks.push(prop.remarks.toLowerCase());
        layer.bindPopup("<h4>"+prop.code+"</h4><hr>"+"<p>Name : "+prop["Project Name"]+"</p>");
       layer.on('click', function(){
         displayProperties(feature.properties);
       });

    }

}

</script>
</body>
</html>

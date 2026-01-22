<?php

require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';
require_once 'modules/FloorScheme/models/FloorSchemeModel.php';
vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.Controller');
vimport('includes.runtime.LanguageHandler');

global $current_user;
$current_user = Users::getActiveAdminUser();

$floors = FloorScheme_Model::listAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script>
    var floors = JSON.parse(`<?php echo json_encode($floors) ?>`);
  </script>
  <style>
    <?php include('layouts/v7/modules/Workspace/resources/style.css') ?>
  </style>
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    .scheme-wrapper {
      margin: 0;
    } 

    #scheme {
      margin: 0 !important;
    }
  </style>
</head>
<body>
  <div class="scheme-wrapper p-3">
    <div id="scheme"></div>
  </div>
  <script>
    <?php include('libraries/jquery/jquery.min.js') ?>
  </script>
  <script>
    <?php include('libraries/pointIntPolygon/pointIntPolygon.js') ?>
  </script>
  <script>
    <?php include('layouts/v7/modules/Workspace/resources/FloorsSchemeAdapter.js') ?>
  </script>
  <script>
    <?php include('layouts/v7/modules/Workspace/resources/FloorsScheme.js') ?>
  </script>
  <script>
    const convertedData = FloorsSchemeAdapter.convertData(floors);

    const floorScheme = new FloorsScheme('#scheme', convertedData, {
      editMode: false,
    });
    floorScheme.initialize();

  </script>
</body>
</html>
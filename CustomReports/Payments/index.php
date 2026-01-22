<?php ini_set("display_errors", 1); header('Content-Type: text/html; charset=utf-8'); 
    require_once "../../config.inc.php";
    $db = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
    mysqli_set_charset($db,"utf8");

    if ($_SERVER['SERVER_NAME'] == 'ideal-home.oimo-billing.mycloud.kg') {
        
?>

<!DOCTYPE html>   
<html lang="en">  
<head>  
    <title>Отчет по финансам по </title>  
    <meta charset="utf-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">  
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>  -->
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
    <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
    <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script> 
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>  
</head>  
<body>  
    <style>
        .subinfo{
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            background-color: #ffccbc;
            width: 100%;
            height: 100%;
            /*z-index: 999999999999;*/
            padding-top: 10%;
        }
        table{
            /*table-layout: fixed;*/
            /*width: 100%;*/
        }
        table th, table td{
            font-size: 12px;
        }
        table th{
            border: 1px solid #000;
            text-align: center;
            width: 150px;
            padding: 5px;
            /*background-color:  #7fdbe7;*/
            color: #000;
            word-break: break-word;
            position: sticky;
            top: 0;
            z-index: 9999;
        }
        table th:nth-child(1) {
            width: 250px;
        }
        
        /*table th:nth-child(4) {
            width: 150px;
        }
        table th:nth-child(7) {
            width: 70px;
        }
        table th:nth-child(10) {
            width: 70px;
        }*/
        table td{
            border: 1px solid #000;
            padding: 0 5px;
            /*width: 100px;*/
            position: relative;
        }
        table tr:hover .subinfo{
            display: block;
        }
        table .Scheduled{
            background-color: #eeee00;
        }
        table .Executed{
            background-color: #4caf50;
        }
        table .Delayed{
            background-color: #ff8a65;
        }
        table a{
            color: #000;
            text-decoration: none;
        }
        .main{
            /*overflow: scroll;*/
            max-width: 1350px;
        }
        /*option{
            display: inline;
        }*/
       
    </style>

<div class="container">  
    <div class="page-header">  
        <h2>Отчет по финансам</h2>      
    </div>  
    <div class="row">  
        <div class="col-md-8 col-md-offset-2"> 
            <?php require_once 'db.php'; ?>
                <form>
                  
                    <div class="col-md-12">
                        <label for="so">Период c</label>
                        <select name="yearstart" id="year">
                            <option value="2016">2016</option>
                            <option value="2017">2017</option>
                            <option value="2018">2018</option>
                            <option value="2019">2019</option>
                            <option value="2020" selected>2020</option>
                            <option value="2021">2021</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                            <option value="2030">2030</option>
                        </select>

                        <label for="so">по </label>
                        <select name="yearend" id="year">
                            <option value="2016">2016</option>
                            <option value="2017">2017</option>
                            <option value="2018">2018</option>
                            <option value="2019">2019</option>
                            <option value="2020">2020</option>
                            <option value="2021">2021</option>
                            <option value="2022" selected>2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                            <option value="2030">2030</option>
                        </select>
                    </div>
                    <?php 

                        $yearStart = $_GET['yearstart'];
                        $yearEnd = $_GET['yearend'];

                     ?>
                    <div class="col-md-12">
                        <br>
                        <div class="col-md-5 col-md-offset-3">
                            
                            <input type="submit" value="Сформировать" class="btn btn-success">
                       
                            <a href="/CustomReports/Payments/excel.php<?php echo '?yearstart='.$yearStart.'&yearend='.$yearEnd ?>" class="btn btn-info">Выгрузить</a>
                       
                        </div>
                        <br><br>
                    </div>                   

                </form>
        </div>
    </div>    
</div>
                   
                            <div class="main">
                           
                            <?php

                                require 'query.php';
                               /* foreach ($user as $key => $value) {
                                    $resul = $db->query("SELECT  last_name, first_name from vtiger_users where id = '$value'");
                                    $rows = $resul->fetch_assoc();
                                    echo $rows['last_name'].' '.$rows['first_name'];
                                    echo '<br>';
                                }
                                echo '<br></br>';*/
                            ?>

                            <br><br>
                            </div>


    </body>  
</html>  

<?php } ?>
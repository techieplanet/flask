<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css"  crossorigin="anonymous">
    
    <!-- Fontawesome css -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" >
    
    <!-- JQuery UI css -->
    <link rel="stylesheet" href="css/jquery-ui.min.css" >
    <link rel="stylesheet" href="css/jquery-ui.theme.min.css" >
    <!-- JQuery DataTable CSS -->
    <link rel="stylesheet" href="http://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" type="text/css">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="css/main.css">
    
    
    
   
     
  </head>
  <body>
    
  <nav class="navbar" style="margin-bottom:0;">
    <a href="" class="navbar-brand"><img src="img/logo.png" alt="FP Dashboar Logo" /></a> 
<!--          <ul class="nav navbar-nav pull-xs-right">
              <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
              <li class="nav-item"><a href="#" class="nav-link">ABOUT</a></li> 
         </ul>-->
  </nav>
      <hr style="margin:0;" />    
  
      <div class="container-fluid" style="background-color: #f2f2f2;position: relative;min-height: 700px;">
          
          <!-- Start of Dashboard Content wrapper -->
          <div class="row" style="" >
              
              
              <!-- container for dashboard sidebar -->
              <div id="sidebar_tree" class="col-sm-2" style="background-color: #f2f2f2; padding-top: 10px; padding-bottom: 10px; ">
                  
                  <ul class="nav">
                      <li class="nav-item ">
                          <a href="#" class=" nav-link active_sidebar_link"  onclick="loadView('dashboard-overview.html');return false"><i class="fa fa-dashboard"></i>&nbsp; Overview</a>
                      </li>
                       <hr />
                      <li class="nav-item ">
                          <a href="#" class="tree-toggle nav-link"><i class="fa fa-cube"></i>&nbsp; Modules</a>
                          <ul class="tree">
                              <li class="nav-item"><a href="#" class="nav-link" onclick="loadView('modules/modules.html #modules-chart'); return false" >Charts</a></li>
                              <li class="nav-item"><a href="#" class="nav-link" onclick="loadView('modules/modules.html #modules-query'); return false">Queries</a></li>
                              <li class="nav-item"><a href="#" class="nav-link" onclick="loadView('modules/modules.html #modules-datacollection'); return false">Data Collection</a></li>
                          </ul>
                      </li>
                      <hr />
                     
                      <li class="nav-item">
                          <a href="#" class="tree-toggle nav-link"><i class="fa fa-users"></i>&nbsp; Users</a>
                          <ul class="tree">
                          <li class="nav-item"><a href="#" class="nav-link" onclick="loadView('user/user.html #user-overview'); return false">User Activity</a></li>
                          <li class="nav-item"><a href="#" class="nav-link" onclick="loadView('user/user.html #user-list'); return false">User's List</a></li>
                          </ul>
                      </li>
                  </ul>
                  
              </div>
              <!-- End of dashboard sidebar -->
              
              
  <!-- ************************************************************************************************************** -->            
              
              <div class="col-sm-10" style="padding-top:10px; padding-bottom: 30px; background-color: #ECECFB;min-height:700px">
                  
                  
                  <!-- container for dashboard top widget area -->
                  <div class="row">
                      
                      <div class="col-sm-3">
                          <div class="tp_widget_left_c" style="background-color: #F39C12;"><i class="fa fa-users fa-3x"></i></div>
                          <div class="tp_widget_right_c">
                              <h6 class="text-xs-center">Total Registered Users</h6>
                              <p class="text-xs-center" id="total_registered_users"></p>
                          </div>
                      </div>
                      
                      
                      <div class="col-sm-3">
                          <div class="tp_widget_left_c" style="background-color: #007fff;"><i class="fa fa-lock fa-3x"></i></div>
                          <div class="tp_widget_right_c">
                              <h6 class="text-xs-center">Total Daily User Logins</h6>
                              <p class="text-xs-center" id="total_daily_logins"></p>
                          </div>
                      </div>
                      
                      <div class="col-sm-3">
                          <div class="tp_widget_left_c" style="background-color: #008C00;"><i class="fa fa-plus-square fa-3x"></i></div>
                          <div class="tp_widget_right_c">
                              <h6 class="text-xs-center">Current Active Users</h6>
                              <p class="text-xs-center">6</p>
                          </div>
                      </div>
                      
                      <div class="col-sm-3">
                          <div class="tp_widget_left_c" style="background-color: #FF4000;"><i class="fa fa-gears fa-3x"></i></div>
                          <div class="tp_widget_right_c">
                              <h6 class="text-xs-center">Total Daily Sessions</h6>
                              <p class="text-xs-center">11</p>
                          </div>
                      </div>
                          
                  </div>
                  
                  <div id="loader" class="text-xs-center" style="display:none; vertical-align: middle">
                      <i class="fa fa-spinner fa-2x fa-spin" style="color: #008C00"></i>
                  </div>
                 <!-- End of dashboard top widget area -->
    
    <!--  *********************************************************************************************************** -->     
    
    
              <!-- Dashboard Overview Container -->
              <div class="row" id="dashboard-display" >
                  
                  
                  
                  <div class="col-xs-12" id="dashboard-chart1">
                         <div class="card">
                             
                             <div class="card-header" style="background-color:#008C00; color:#fff">  
                             </div>
                             
                             <div class="card-block">
                                 <div style="background-color:#f7f7f7; border:1px solid #eee; padding:15px 0 0 15px; margin-bottom:15px">
                                     <form id="dashboard_form" class="form-group" >
                                         
                                         <select id="province_id" class="form-control-sm" size="7" multiple>
                                                   <option>--Select Zone--</option>
                                               </select>
                                         
                                              <select id="district_id" class="form-control-sm" size="7" multiple>
                                                   <option>--Select State--</option>
                                               </select>
                                         
                                              <select id="region_c_id" class="form-control-sm" size="7" multiple>
                                                   <option>--Select LGA--</option>
                                               </select>
                                         <button class="btn btn-success btn-sm">Filter</button>
                                     </form>
                                 </div>
                                  <div id="logins_barchart">
                                      <!--daily user logins bar chart-->
                                  </div>
                                  <p>
                                      Sum of daily user log-ins for all geographies for each of the last 12 months inclusive
                                      of the most recent whole month in a line chart
                                  </p>
                                  <span>Vertically stacked</span>
                             </div>
                             
                      </div>
                 </div>
                  
<!--                  <div class="col-xs-12" style="margin-top:20px">
                      
                      <div class="card">
                          <div class="card-header" style="background-color:#008C00; color:#fff">
                              <span>Details By User</span>  <a href="#" class="pull-xs-right" onclick="return false;" style="color:#fff" title="This table depicts Top 10 users activity "><i class=" fa fa-info-circle fa"></i></a>
                          </div>
                          <div class="card-block">
                              
                              <table class="table table-striped">
                                  <thead>
                                        <th>User Name</th>
                                        <th>Role</th>
                                        <th>Sessions</th>
                                        <th>Last Login Date</th>
                                        <th>Current Login Status</th>
                                  </thead>
                                  <tbody>
                                      <tr>
                                          <td>Awe Ayobami</td>
                                          <td>FMOH</td>
                                          <td>43</td>
                                          <td>6/28/2016</td>
                                          <td>Yes</td>
                                      </tr>
                                      <tr>
                                          <td>Awe Ayobami</td>
                                          <td>FMOH</td>
                                          <td>43</td>
                                          <td>6/28/2016</td>
                                          <td>No</td>
                                      </tr>
                                      <tr>
                                          <td>Awe Ayobami</td>
                                          <td>FMOH</td>
                                          <td>43</td>
                                          <td>6/28/2016</td>
                                          <td>Yes</td>
                                      </tr>
                                  </tbody>
                              </table>
                              
                          </div>
                          
                      </div>
                      
                  </div>-->
                  
                  
                  <!-- DETAILS BY LOCATION TABLE -->
                  <div class="col-xs-12" style="margin-top:20px">
                       
                       
                       
                      <div class="card">
                          <div class="card-header" style="background-color:#008C00; color:#fff">
                              <span>Details By Location</span>  <a href="#" style="color:#fff" onclick="return false;" class="pull-xs-right" title="This table displays each of the four indicators in the top line (registered users, user logins, active users, sessions) geographical data"><i class="fa fa-info-circle fa"></i></a>
                          </div>
                          <div class="card-block">
                              
                              
                              
                              <table class="table table-striped" id="locationTable">
                                  <thead>
                                        <th>Location</th>
                                        <th>Total Registered Users</th>
                                        <th>Total Daily Users Logins</th>
                                        <th>Current Active Users</th>
                                        <th>Total Daily Sessions</th>
                                  </thead>
                                  <tbody>
                                      <tr>
                                          <td>North Central</td>
                                          <td>56</td>
                                          <td>34</td>
                                          <td>24</td>
                                          <td>39</td>
                                      </tr>
                                      <tr>
                                          <td>South West</td>
                                          <td>56</td>
                                          <td>34</td>
                                          <td>24</td>
                                          <td>39</td>
                                          
                                      <tr>
                                          <td>South East</td>
                                          <td>56</td>
                                          <td>34</td>
                                          <td>24</td>
                                          <td>39</td>
                                          
                                      </tr>
                                  </tbody>
                              </table>
                              
                          </div>
                          
                      </div>
                      
                  </div>
                  
                  
                  
                  
              </div>
              <!-- Dashboard Overview Container Ends here -->
                  
              
        </div>
             
           
          </div>
          <!-- End of Dashboard Content wrapper -->
          
      </div><!-- Main Container div ends here  -->

    <!-- jQuery first, then Bootstrap JS. -->
    <script src="js/jquery-1.12.0.min.js"></script>
    <script src="js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="http://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="js/charts.js"></script>
    
     <script>
      $(function() {
        //set high charts global color scheme for all high charts instances on this page
        Highcharts.setOptions({
                    colors: ['#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#0099C6', '#DD4477', '#AAAA11', '#B77322']
                });
      });
      
     
      locationData = {};
      userProfileData = {};
     
      loadLocationData();
      getTotalRegisteredUsers();
      getUserLogins(); //Gets Daily user Logins
      getUserProfileData();
      
      //global variable for callback methods
      var globalJson = '';
      
      //Dashboard Chart: setUpDailyUserLoginsByLocatioinChart
      genericAjax('../analyticsQuery/getUserLoginsByLocation', {}, setUpDailyUserLoginsByLocatioinChart);
      
      $(document).ready(function(){
           
           
           
           $('.tree-toggle').click(function (event) {
             event.preventDefault();
           $(this).parent().children('ul.tree').toggle(200);
            
           });
           
           //Code for adding clicked district_id in sidebar
           $("#sidebar_tree a").click(function()
           {
             if (!$(this).hasClass('tree-toggle')) {
               $("#sidebar_tree a").each(function()
               {
                   $(this).removeClass("active_sidebar_link");
               })
               
               $(this).addClass("active_sidebar_link");
               
              }
           });
           
           initUI();
      })
      
      //functions that needs to run when we load new pages
      function initUI() {
         
        updateZone();
        updateUsersList();
        
        $("#locationTable").dataTable();
        
        $("#province_id").on("change",function(){updateState("1")});
        $("#province_id2").on("change",function(){updateState("2")});
        
        $("#district_id").on("change",updateLGA);
        $("#district_id2").on("change",updateLGA);
        
        $("#tabs").tabs();
           
        $(".calendar").datepicker();
        $("#toDate2").datepicker();
        $("#fromDate2").datepicker();
           
       $(document).tooltip({
           show: {
            effect: "slideDown",
            delay: 250
             }

       });

       $("#accordion").accordion();
       
            /************** CALL CHARTS HERE ********************/
            
      }
      //End of initUI() function
      
      function startLoading()
      {
          $("#loader").dialog({
              modal : true,
              dialogClass: "no-close",
              resizable: false,
              height : 70,
              width : 150
          });
      }
      
      function stopLoading()
      {
         $("#loader").dialog('close');
      }
      
      function loadView(page) {
          startLoading();
          $("#dashboard-display").load("partials/"+page,function(response,status,xhr)
          {
              initUI();
              stopLoading();
          }); 
      }
      
      function loadLocationData()
      {
          console.log("Entered Load location data")
          $.ajax({
              url : '../analyticsQuery/getLocationData',
              type : 'post',
              success : function(res)
              {
                  console.log("Ajax Successful")
                  //console.log(JSON.stringify(res))
                  locationData = JSON.parse(res)
                  updateZone();
                  
              },
              error: function(xhr,errcode,text){
                  console.log("Error.... " + errcode + " - " +  text);
              }
          });
      }
      
      function getLocationId(locationName)
      {
        console.log("location = " + locationName);
        
        if(locationName == null)
        {
            return new Array();
        }
        
          var data = locationData;
          var location_ids = [];
          
        for(var j=0; j<locationName.length;j++)
        {
          for(var i=0; i<data.length; i++)
          {
              if(locationName[j] == data[i].location_name)
              {
                  location_ids.push(data[i].id);
                  break;
              }
          }
        }
        return location_ids;
      }
      
      function updateZone()
      {
          if(("#province_id").length)
          {
              var data = locationData;
              for(var i=0; i<data.length;i++)
              {

                      if(data[i].tier == 1 || data[i].tier == "1")
                      {
                           
                           var loc = data[i].location_name;
                          var opt = "<option value='"+ loc +"'>" + data[i].location_name + "</option>";
                          $("#province_id").append(opt);
                      }

              }
              
              updateState("1");
          }
          if(("#province_id2").length)
          {
             
                  var data = locationData;
                  for(var i=0; i<data.length;i++){


                          if(data[i].tier == 1 || data[i].tier == "1")
                          {
                               console.log("found match");
                              var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                              $("#province_id2").append(opt);
                          }

                  }

                 updateState("2");
          }
          
      }
      
      function updateState(id)
      {
          
          var data = locationData;
          
          
          if(id == "1" || id == 1)
          {
              console.log("province_id 1 changed")
              var province_id_ids = getLocationId($("#province_id").val());
              console.log(province_id_id);
              $("#district_id").html("");
              $("#district_id").html("<option>--Select State--</option>");
              
            for(var j=0;j<province_id_ids.length;j++)
            {
              var province_id_id = province_id_ids[j];
              for(var i=0; i<data.length;i++)
              {


                      if(data[i].parent_id == province_id_id )
                      {
                          if(data[i].location_name == "Federal Capital Territory")
                          {
                              var opt = "<option value='"+ data[i].location_name +"'>FCT</option>";
                          }
                          else{
                              console.log(data[i].location_name)
                          var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                          }

                          $("#district_id").append(opt);
                      }

              }
            }
         }
         else if(id == "2" || id == 2)
         {
              console.log("province_id 2 changed")
             var province_id_ids = getLocationId($("#province_id2").val());
             
             $("#district_id2").html("");
             $("#district_id2").html("<option>--Select State--</option>");
              //$("#district_id").append("<option>--Select State--</option>");
              
              
            for(var j=0;j<province_id_ids.length;j++)
            {
              var province_id_id = province_id_ids[j];  
              
              for(var i=0; i<data.length;i++)
              {


                      if(data[i].parent_id == province_id_id )
                      {
                          if(data[i].location_name == "Federal Capital Territory")
                          {
                              var opt = "<option value='"+ data[i].location_name +"'>FCT</option>";
                          }
                          else{
                          var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                          }

                          $("#district_id2").append(opt);
                      }

              }
            }
         }
          
          updateLGA();
      }
      
      function updateLGA()
      {
            if($("#region_c_id").length) 
            {
                      var data = locationData;
                      var district_id_ids = getLocationId($("#district_id").val());
                      $("#region_c_id").html("");
                      $("#region_c_id").html("<option>--Select LGA--</option>");

                for(var j=0;j<district_id_ids.length;j++)
                {
                  var district_id_id = district_id_ids[j];  

                      for(var i=0; i<data.length;i++)
                      {
                          if(data[i].parent_id == district_id_id )
                              {
                                   console.log("found region_c_id match");
                                  var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                                  $("#region_c_id").append(opt);
                              }
                      }
                }
            }
      }
      
      function getTotalRegisteredUsers()
      {
          startLoading();
          $.ajax({
              url : '../analyticsQuery/getTotalRegisteredUsers',
              type : 'post',
              success: function(res)
              {
                  console.log("Total user : " + res);
                  $("#total_registered_users").html(res);
                  stopLoading();
              },
              error: function(xhr,status,text)
              {
                  console.log("Error " + status + " - " + text);
                  stopLoading();
              }
          });
      }
      
      function getUserLogins()
      {
          
          
          startLoading();
          $.ajax({
              url : '../analyticsQuery/getUserLogins',
              type : 'post',
              success: function(res)
              {
                  console.log("Total user : " + res);
                  $("#total_daily_logins").html(res);
                  stopLoading();
              },
              error: function(xhr,status,text)
              {
                  console.log("Error " + status + " - " + text);
                  stopLoading();
              }
          });
      }
      
      function getUserProfileData()
      {
          
          
          $.ajax({
              url : '../analyticsQuery/getUserprofile',
              success : function(res){
                  
                  userProfileData = JSON.parse(res);
                  updateUsersList();
              }
          })
      }
      
      
      //function setUpDailyUserLoginsByLocatioinChart(url, callback){
      function genericAjax(url, formData, callback){
          //URL FORM: '../analyticsQuery/getUserprofile'
          
          $.ajax({
              url : url, 
              data: formData,
              success : function(res){
                  //console.log('this is the response: ' + res);
                  callback(res);
              }
          })
      }
      
     
      
      function updateUsersList()
      {
          if($("#user_list_group").length)
          {
              var data = userProfileData;
              
              for(var i=0; i < data.length; i++)
              {
                  var fullname = data[i].last_name + ' ' + data[i].first_name;
                  var btn = '<button type="button" class="list-group-item">' + fullname + '</button>' ;
                  $("#user_list_group").append(btn);
              }
          }
      }
      
      
      
    </script>
  </body>
</html>
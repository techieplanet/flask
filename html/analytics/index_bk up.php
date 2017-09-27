

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
    <script src="js/jquery-1.12.0.min.js"></script>
    
    <script>
    
      var dashboardUrl = "";
      
      function redirectUser()
      {
          
          
          var currentUri = encodeURIComponent(location.href);
          var url = '../user/login/?redirect='+currentUri;
          
          location.href = url;
          //stopLoading();
      }
      
        function isLoggedIn()
      {
          
          $.ajax({
              url : '../analyticsQuery/checkLogin',
              type : 'post',
              async : false,
              success : function(res)
              {
                  var data = JSON.parse(res);
                  dashboardUrl = data.dashboardUrl;
                  
                  if(data.ok == "0" || data.ok == 0)
                  {
                      redirectUser();
                  }
                 
              }
          });
      }
      
      isLoggedIn();
    
    </script>
  </head>
  <body>
    
  
      
  <nav class="navbar" style="margin-bottom:0;">
      <div class="row">
          <div class="col-md-2">
    <a href="" id="headerImg" class="navbar-brand"><img src="img/logo.png" alt="FP Dashboar Logo" /></a> 
          </div>
<!--          <ul class="nav navbar-nav pull-xs-right">
              <li class="nav-item"><a href="#" class="nav-link">Home</a></li>
              <li class="nav-item"><a href="#" class="nav-link">ABOUT</a></li> 
         </ul>-->
<div class="col-md-2 col-md-push-8">
    <br/>
    <b>Usage data in this section is from June,2016 to date</b>
</div>
      </div>
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
                          <li class="nav-item"><a href="#" class="nav-link" onclick="loadView('user/user.html #user-list'); return false">User List</a></li>
                          </ul>
                      </li>
                  </ul>
                  
              </div>
              <!-- End of dashboard sidebar -->
              
              
  <!-- ************************************************************************************************************** -->            
              
              <div class="col-sm-10" style="padding-top:10px; padding-bottom: 30px; background-color: #ECECFB;min-height:700px">
                  
                  
                  <!-- container for dashboard top widget area -->
                  <div class="row" id="topLineWidget">
                      
                      <div class="col-sm-3" title="The total number of users who have registered user accounts since the dashboard was started in May 2015">
                          <div class="tp_widget_left_c" style="background-color: #F39C12;"></div>
                          <div class="tp_widget_right_c">
                              <p class="text-xs-center make-bold">Total registered users, to date</p>
                              <p class="text-xs-center tp_widget_placeholder" id="total_registered_users"></p>
                          </div>
                      </div>
                      
                      
                      <div class="col-sm-3" title="The total number of successful user logins on the indicated date. Note that even if a user logs in more than once in a day, they are counted only once">
                          <div class="tp_widget_left_c" style="background-color: #007fff;"></div>
                          <div class="tp_widget_right_c">
                              <p class="text-xs-center make-bold ">Total daily user logins, for the day <span class="yesterday"></span></p>
                              <p class="text-xs-center tp_widget_placeholder" id="total_daily_logins"></p>
                          </div>
                      </div>
                      
                      <div class="col-sm-3" title="The total number of users who are currently logged in to the dashboard and taking an action ">
                          <div class="tp_widget_left_c" style="background-color: #008C00;"></i></div>
                          <div class="tp_widget_right_c">
                              <p class="text-xs-center make-bold " style="margin-bottom: 25px">Current active users</p>
                              <p class="text-xs-center tp_widget_placeholder" id="current_active_users"></p>
                          </div>
                      </div>
                      
                      <div class="col-sm-3" title="The total number of sessions on the indicated date. Each time a user logs in, it is counted as a separate session">
                          <div class="tp_widget_left_c" style="background-color: #FF4000;"></div>
                          <div class="tp_widget_right_c">
                              <p class="text-xs-center make-bold " style="margin-bottom: 25px">Total daily sessions, for the day <br> <span class="yesterday"></span></p>
                              <p class="text-xs-center tp_widget_placeholder" id="total_daily_sessions" ></p>
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
                                     <form id="dashboard_form" class="form-group" onsubmit="return false" >
                                         
                                               <select id="province_id" name="province_id[]"  class="form-control-sm select-max-6" size="7" multiple>
                                                   <option>--Select Zone--</option>
                                               </select>
                                         
                                               <select id="district_id" name="district_id[]" class="form-control-sm select-max-6" size="7" multiple>
                                                   <option>--Select State--</option>
                                               </select>
                                         
                                              <select id="region_c_id" name="region_c_id[]" class="form-control-sm select-max-6" size="7" multiple>
                                                   <option>--Select LGA--</option>
                                               </select>
                                         <button class="btn btn-success btn-sm" onclick="getDailyUserLoginsByLocatioin()">Filter</button>
                                     </form>
                                     <div><span>Maximum of six (6) locations allowed per filter</span></div>
                                 </div>
                                  <div id="logins_barchart">
                                      <!--daily user logins bar chart-->
                                  </div>
                                 <div id="dashboard-linechart">
                                     
                                 </div>
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
                              <span>Details by location</span>  <a href="#" style="color:#fff" onclick="return false;" class="pull-xs-right" title="This table displays each of the four indicators in the top line (registered users, user logins, active users, sessions) geographical data"><i class="fa fa-info-circle fa"></i></a>
                          </div>
                          <div class="card-block">
                              <div style="background-color:#f7f7f7; border:1px solid #eee; padding:15px 0 0 15px; margin-bottom:15px">
                                  <form id="dashboard_form2" class="form-group" onsubmit="return false" >
                                         
                                               <select id="province_id" name="province_id[]" class="form-control-sm select-max-6" size="7" multiple>
                                                   <option>--Select Zone--</option>
                                               </select>
                                         
                                               <select id="district_id" name="district_id[]" class="form-control-sm select-max-6" size="7" multiple>
                                                   <option>--Select State--</option>
                                               </select>
                                         
                                               <select id="region_c_id" name="region_c_id[]" class="form-control-sm select-max-6" size="7" multiple>
                                                   <option>--Select LGA--</option>
                                               </select>
                                      <button class="btn btn-success btn-sm" onclick="getUsersByLocation()">Filter</button>
                                 </form>
                                  <div><span>Maximum of six (6) locations allowed per filter</span></div>
                              </div>     
                                  
                              
                              <table class="table table-striped" id="usersByLocationTable">
                                  <thead>
                                        <th style="text-align:center">Location</th>
                                        <th style="text-align:center">Total registered users</th>
                                        <th style="text-align:center">Total daily users logins for <span class="overview-table"></span></th>
                                        <th style="text-align:center">Current active users</th>
                                        <th style="text-align:center">Total daily sessions for <span class="overview-table"></span></th>
                                  </thead>
                                  <tbody id="usersByLocationTableBody">
                                      
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
    
    <script src="js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="http://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="js/charts.js"></script>
    <script src="js/analyticsTables.js"></script>
    <script src="js/usersList.js"></script>
    
     <script>
      
      $(function() {
        //set high charts global color scheme for all high charts instances on this page
        Highcharts.setOptions({
                    colors: ['#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#0099C6', '#DD4477', '#AAAA11', '#B77322']
                });
      });
      
     
      locationData = {};
      usersList = {};
      
      
        $(document).ajaxStart(function(){
                startLoading();
        });

        $(document).ajaxStop(function(){
                stopLoading();
        });
        $(document).ajaxError(function(){
                console.log('Error stopping spinner');
                stopLoading();
        });
        
      loadLocationData();
      getTotalRegisteredUsers();
      getUserLogins(); 
      getCurrentActiveUsers();
      getUserTotalSessions();
          
          
      
      
      //global variable for callback methods
      var globalJson = '';
      
      //Dashboard Chart: setUpDailyUserLoginsByLocatioinChart
      
      
      $(document).ready(function(){
           
           //Set href for banner logo
           $("#headerImg").attr("href",dashboardUrl);
           
           initUI();
           
           
           genericAjax('../analyticsQuery/getUserLoginsLastMonthsByLocation', {}, setUpUserLoginsLastMonthsByLocationChart);
           genericAjax('../analyticsQuery/getUserLoginsByLocation', {}, setUpDailyUserLoginsByLocatioinChart);
           genericAjax('../analyticsquery/getDetailsByLocation',{},setUpUsersByLocationTableBody);
           
           $('.tree-toggle').click(function (event) {
             event.preventDefault();
           $(this).parent().children('ul.tree').toggle(200);
            
           });
           
           //Code for adding clicked module in sidebar
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
           
           
      })
      
      //functions that needs to run when we load new pages
      function initUI() {
         getYesterdayDate();
         
        $(document).ajaxStart(function(){
                console.log('loading started');
                startLoading();
        });

        $(document).ajaxStop(function(){
                console.log('loading stopped');
                stopLoading();
        });
        
        $(document).ajaxError(function(){
                console.log('Error stopping spinner');
                stopLoading();
        });
        
//        $(document).ajaxError(function(){
//                alert('Ooops, Something went wrong, Make sure you are logged into dashboard before using Analytics');
//                stopLoading();
//        });
//         
        updateZone();
        
        //Limit Geography selection to 6
        $(".select-max-6").change(function (event) {
              
              var counter = 0;
              $(this).find('option:selected').each(function(){
                  if(counter > 6){
                      alert("Maximum Selection allowed is 6");
                      return;
                  }
                  counter++;
              });
              
//              if($(this+" option:selected").length > 6) {
//                  
//                  alert("Maximum Selection allowed is 6");
//                  
//              }
       });
       
       
        $("#dashboard_form #province_id").on("change",function(){updateState("1")});
        $("#dashboard_form2 #province_id").on("change",function(){updateState("2")});
        
        $("#dashboard_form #district_id").on("change",function(){updateLGA("1")});
        $("#dashboard_form2 #district_id").on("change",function(){updateLGA("2")});
        
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

       //$("#accordion").accordion();
       
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
      
      function stopLoading(){
         $("#loader").dialog('close');
      }
      
      function loadView(page) {
          
          $("#dashboard-display").load("partials/"+page,function(response,status,xhr)
          {
              initUI();
              if(page == 'modules/modules.html #modules-chart')
              {
                  genericAjax('../analyticsquery/getDailySessionsByChartSubButton',{},setUpDailySessionsByChartSubButton);
                  
              }
              else if(page == 'modules/modules.html #modules-query')
              {
                  genericAjax('../analyticsquery/getDailySessionsByQueries',{},setUpDailySessionsByQueries);
                  genericAjax('../analyticsquery/getDailySessionsLastMonthsByQueries',{},setUpDailySessionsLastMonthsByQueries);
                  
              }
              else if(page == 'modules/modules.html #modules-datacollection')
              {
                  genericAjax('../analyticsquery/getDailySessionsByDataCollection',{},setUpDailySessionsByDC);
                  genericAjax('../analyticsquery/getDailySessionsLastMonthsByDc',{},setUpDailySessionsLastMonthsByDc);
                  genericAjax('../analyticsquery/getRecentDcEvent',{},setUpRecentDcEventsTable);
              }
              else if(page == 'user/user.html #user-overview')
              {
                  genericAjax('../analyticsquery/getSumTotalUsersByGeo',{},setUpSumTotalUsersByGeo);
                  genericAjax('../analyticsquery/getSumTotalUsersLast12Months',{},setUpSumTotalUsersLast12Months);
                  getDetailsByUserTable();
                 
              }
              else if(page == 'user/user.html #user-list')
              {
                  var formData = "mode=all";
                  genericAjax('../analyticsquery/getUserprofile',formData,setUpUsersList);
                  setUpAutocomplete();
                 
              }
              else if(page == 'dashboard-overview.html')
              {
                   genericAjax('../analyticsQuery/getUserLoginsLastMonthsByLocation', {}, setUpUserLoginsLastMonthsByLocationChart);
                   genericAjax('../analyticsQuery/getUserLoginsByLocation', {}, setUpDailyUserLoginsByLocatioinChart);
                   genericAjax('../analyticsquery/getDetailsByLocation',{},setUpUsersByLocationTableBody);
              }
             
          }); 
      }
      
      function loadLocationData(){
         
          $.ajax({
              url : '../analyticsQuery/getLocationData',
              type : 'post',
              success : function(res)
              {
                  //console.log(JSON.stringify(res))
                  locationData = JSON.parse(res)
                  updateZone();
                  
              },
              error: function(xhr,errcode,text){
                  console.log("Error.... " + errcode + " - " +  text);
              }
          });
      }
      
      function getLocationId(locationName){
        
        
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
      
      function updateZone() {
          if(("#dashboard_form #province_id").length)
          {
              var data = locationData;
              for(var i=0; i<data.length;i++)
              {

                      if(data[i].tier == 1 || data[i].tier == "1")
                      {
                           
                           var loc = data[i].location_name;
                          var opt = "<option value='"+ loc +"'>" + data[i].location_name + "</option>";
                          $("#dashboard_form #province_id").append(opt);
                      }

              }
              
              updateState("1");
          }
          if(("#dashboard_form2 #province_id").length)
          {
             
                  var data = locationData;
                  for(var i=0; i<data.length;i++){


                          if(data[i].tier == 1 || data[i].tier == "1")
                          {
                               console.log("found match");
                              var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                              $("#dashboard_form2 #province_id").append(opt);
                          }

                  }

                 updateState("2");
          }
          
      }
      
      function updateState(id) {
          
          var data = locationData;
          
          
          if(id == "1" || id == 1)
          {
              
              var province_id_ids = getLocationId($("#dashboard_form #province_id").val());
              //console.log(province_id_id);
              $("#dashboard_form #district_id").html("");
              $("#dashboard_form #district_id").html("<option>--Select State--</option>");
              
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

                          $("#dashboard_form #district_id").append(opt);
                      }

              }
            }
            updateLGA("1");
         }
         else if(id == "2" || id == 2)
         {
              
             var province_id_ids = getLocationId($("#dashboard_form2 #province_id").val());
             
             $("#dashboard_form2 #district_id").html("");
             $("#dashboard_form2 #district_id").html("<option>--Select State--</option>");
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

                          $("#dashboard_form2 #district_id").append(opt);
                      }

              }
            }
            updateLGA("2");
         }
          
          
      }
      
      function updateLGA(id){
            if(id == 1 || id == "1") 
            {
                      var data = locationData;
                      var district_id_ids = getLocationId($("#dashboard_form #district_id").val());
                      $("#dashboard_form #region_c_id").html("");
                      $("#dashboard_form #region_c_id").html("<option>--Select LGA--</option>");

                for(var j=0;j<district_id_ids.length;j++)
                {
                  var district_id_id = district_id_ids[j];  

                      for(var i=0; i<data.length;i++)
                      {
                          if(data[i].parent_id == district_id_id )
                              {
                                   
                                  var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                                  $("#dashboard_form #region_c_id").append(opt);
                              }
                      }
                }
            }
            else
            {
                     var data = locationData;
                      var district_id_ids = getLocationId($("#dashboard_form2 #district_id").val());
                      $("#dashboard_form2 #region_c_id").html("");
                      $("#dashboard_form2 #region_c_id").html("<option>--Select LGA--</option>");

                for(var j=0;j<district_id_ids.length;j++)
                {
                  var district_id_id = district_id_ids[j];  

                      for(var i=0; i<data.length;i++)
                      {
                          if(data[i].parent_id == district_id_id )
                              {
                                   
                                  var opt = "<option value='"+ data[i].location_name +"'>" + data[i].location_name + "</option>";
                                  $("#dashboard_form2 #region_c_id").append(opt);
                              }
                      }
                }
            }
      }
      
      function getTotalRegisteredUsers(){
          
          $.ajax({
              url : '../analyticsQuery/getTotalRegisteredUsers',
              type : 'post',
              success: function(res)
              {
                  $("#total_registered_users").html(res);
                  
              },
              error: function(xhr,status,text)
              {
                  console.log("Error " + status + " - " + text);
                  
              }
          });
      }
      
      function getUserLogins(){
          
          
          
          $.ajax({
              url : '../analyticsQuery/getUserLogins',
              type : 'post',
              success: function(res)
              {
                  $("#total_daily_logins").html(res);
                  
              },
              error: function(xhr,status,text)
              {
                  console.log("Error " + status + " - " + text);
                  
              }
          });
      }
      
      
      function getCurrentActiveUsers(){
          
          
          
          $.ajax({
              url : '../analyticsQuery/getCurrentActiveUsers',
              type : 'post',
              success: function(res)
              {
                  //$("#total_daily_logins").html(res);
                  $("#current_active_users").html(res);
                  
              },
              error: function(xhr,status,text)
              {
                  console.log("Error " + status + " - " + text);
                  
              }
          });
      }
      
      
      function getUserTotalSessions(){
          
          
          
          $.ajax({
              url : '../analyticsQuery/getUserDailySessions',
              type : 'post',
              success: function(res)
              {
                  //$("#total_daily_logins").html(res);
                  $("#total_daily_sessions").html(res);
                  
              },
              error: function(xhr,status,text)
              {
                  console.log("Error " + status + " - " + text);
                  
              }
          });
      }
      
      
      //function setUpDailyUserLoginsByLocatioinChart(url, callback){
      function genericAjax(url, formData, callback){
          //URL FORM: '../analyticsQuery/getUserprofile'
         
          $.ajax({
              url : url, 
              type : 'POST',
              data: formData,
              timeout: 600000,
              success : function(res){
                  //console.log('this is the response: ' + res);
                  
                  callback(res);
                
              }
          })
      }
      
      function getYesterdayDate(){
          
          var monthsArray = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
          var date = new Date(new Date().setDate(new Date().getDate()-1));
          
          var year = date.getFullYear();
          var month = date.getMonth();
          var day = date.getDate();
          
          var yesterday = monthsArray[month] + " " + day + " " + year;
          
          $("#topLineWidget .yesterday").each(function()
              {
                 $(this).text(yesterday); 
              });
         
      }
      
      
      
      /**
 *  Onclick Event Handlers go here
 */

//This function listens for click event on {User Over Module} filter button
    function userOverviewFilterClick()
    {
        var formData = $("#dashboard_form").serialize();

        //getSumTotalUsersByGeo() function call reside in chart.js
        getSumTotalUsersByGeo(formData);

        fetchDetailsByUserData(formData);

    }
      
      
    </script>
  </body>
</html>
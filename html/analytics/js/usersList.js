/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var userListTable = null;

function setUpUsersList(userProfileData)
{
  if($("#user_list_group").length)
  {
      var data = JSON.parse(userProfileData);
      $("#user_list_group").html("");

      if(data.length < 1)
      {
          $("#user_list_group").html("<button class='list-group-item'>No Record Found</button>");
      }
      for(var i=0; i < data.length; i++)
      {
          var firstName = data[i].first_name;
          var lastName = data[i].last_name;
          var fullname = lastName.trim() + ' ' + firstName.trim();
          var btn = '<button type="button" class="list-group-item" onclick=getUserDetails("' + data[i].id + '")>' + fullname + '</button>' ;
          $("#user_list_group").append(btn);
      }
  }
}

function fetchUsersList()
{
    var formData = $("#dashboard_form").serialize();
    genericAjax('../analyticsquery/getUserprofile',formData,setUpUsersList);
}

function initAutocomplete(usersJson)
{
    var users = usersJson;
    //console.log(JSON.stringify(users));
    
    $("#userSearchBox").autocomplete({
        source : users,
        minLength : 2,
        select : function(event,ui)
        {
           getUserDetails(ui.item.id);
        }
    });
}

function prepareAutoCompleteSource(usersJson)
{
    var users = JSON.parse(usersJson);
    
    for(var i =0; i<users.length; i++)
    {
        var value = users[i].last_name + " " + users[i].first_name;
        users[i].value = value;
    }
    
    initAutocomplete(users);
}

function setUpAutocomplete()
{
    var formData = "mode=all";
    genericAjax('../analyticsquery/getUserprofile',formData,prepareAutoCompleteSource);
}

function getUserDetails(userid)
{
    $.ajax({
        url : '../analyticsquery/getUserDetails',
        type : 'post',
        data : 'userid='+userid,
        success : function(jsonRes)
        {
            populateUsersDeatails(jsonRes,userid)
        }
    });
    
    getUserSessionHistory(userid,null,null);
}

function populateUsersDeatails(jsonRes,userid)
{
    console.log(JSON.parse(jsonRes));
    var users = JSON.parse(jsonRes);
    
    if(users.profile.length > 0)
    {
        $("#fullname").html(users.profile[0].fullname);
        $("#email").html(users.profile[0].email);
        $("#location").html(users.profile[0].location);
        
        
    }
    else
    {
        $("#fullname").html("");
        $("#email").html("");
        $("#location").html("");
    }
    $("#loggedIn").html(users.loggedIn);
    $("#sessions").html(users.sessions);
    $("#userid").val(userid);
}

function getUserSessionHistory(userid,from,to)
{
    if(from !== null && to !== null)
    {
        var formData = 'userid='+userid+'&from='+from+'&to='+to; 
    }
    else
    {
        var formData = 'userid='+userid;
    }
    
    console.log(formData);
    
    $.ajax({
       
        url : '../analyticsquery/getUserSessionHistory',
        type : 'post',
        data : formData,
        success : function(jsonRes)
        {
            setUpUserSessionHistory(jsonRes);
        }
        
    });
    
}

function setUpUserSessionHistory(jsonRes)
{
    var data = JSON.parse(jsonRes);
    
    $("#accordion").html("");
    
    for(var k in data)
    {
        var count = 1;
        
       
        var loginTime = data[k]['loginTime'];
        var logoutTime = data[k]['logoutTime'];
        var duration = data[k]['duration'];
        
        var h3 = "<h3 style='background-color: #0275D8; color: #fff; '>";
        h3 += "<span style='font-size:11px; font-weight: normal'><b>Login</b> : " + loginTime + "</span> " ;
        h3 += '<span style="font-size:11px; font-weight: normal"><b>Logout</b> : ' + logoutTime + '</span> ';
        h3 += '<span style="font-size:11px; font-weight: normal"><b>Duration</b> : ' + duration + '</span>';
        h3 += "</h3>";
        
        var table = '<div><table class="table table-striped"><thead><th>#</th><th>Page</th><th>Activity</th></thead><tbody>';
        
       // var str = 'Login Time : ' + loginTime + ', Logout Time : ' + logoutTime + ' duration : ' + duration;
       //console.log(str);
        
        //Getting Table contents ID, Page, Activity
        for(var k2 in data[k])
        {
            if(isNaN(k2))
            {
                
                continue;
            }
            var id = count;
            var page = data[k][k2]['page'];
            var activities = data[k][k2]['activities'];
            
            //console.log(id + " <> " + page + " <> " + activities);
            
            var tr = '<tr><td>'+id+'</td><td>'+page+'</td><td>'+activities+'</td></tr>';
            table += tr;
            
            count++;
            
            
        }
        
        table += '</tbody></table></div>';
        var sessionContent = h3 + table;
        $("#accordion").append(sessionContent);
        
    }
    
    
    $("#accordion").accordion({
        heightStyle: "content"
    });
    $("#accordion").accordion('refresh');
    
   
}

function filterUserSessionByDate()
{
    var userid = $("#userid").val();
    
    var from  = $("#fromDate").val();
    var to = $("#toDate").val();
    
    if(userid !== "" && from.length > 0 && to.length > 0)
    {
        getUserSessionHistory(userid,from,to);
    }
}

/**
 * 
 * This function Loads the  users details data, 
 * and passes the json payload to the setUpDetailsByUserTable() function,
 * which populates the DETAILS BY USER TABLE
 */
function getDetailsByUserTable()
{
    $.ajax({
        url : '../analyticsquery/getUsersDetailTable',
        type : 'post',
        success : function(jsonRes)
        {
            setUpDetailsByUserTable(jsonRes)
        }
    })
}

/**
 * 
 * This function gets called on click of the filter button on the filter box.
 * The function fetches Details By User Table Data on the user overview module page.
 * @param {String} formData Serialized form data.
 */
function fetchDetailsByUserData(formData)
{
    genericAjax('../analyticsquery/getUsersDetailTable',formData,setUpDetailsByUserTable);
}

function setUpDetailsByUserTable(jsonRes)
{
    console.log(jsonRes);
    
    var data = JSON.parse(jsonRes);
    
    //console.log(JSON.stringify(data));
    
    if(userListTable != null)
    {
        userListTable.fnDestroy();
        $("#detailsByUserTableBody").html("");
    }
    
    for(var k in data)
    {
        var tr = '<tr>';
        tr += '<td>'+data[k].fullname + '</td>';
        tr += '<td>'+data[k].location + '</td>';
        tr += '<td>'+data[k].role + '</td>';
        tr += '<td>'+data[k].sessions + '</td>';
        tr += '<td>'+data[k].lastLogin + '</td>';
        tr += '<td>'+data[k].loginStatus + '</td>';
        tr += '</tr>';
        
        $("#detailsByUserTableBody").append(tr);
    }
    
        userListTable = $("#detailsByUserTable").dataTable();
    
}

///**
// *  Onclick Event Handlers go here
// */
//
////This function listens for click event on {User Over Module} filter button
//function userOverviewFilterClick()
//{
//    var formData = $("#dashboard_form").serialize();
//    
//    //getSumTotalUsersByGeo() function call reside in chart.js
//    getSumTotalUsersByGeo(formData);
//    
//    fetchDetailsByUserData(formData);
//    
//}
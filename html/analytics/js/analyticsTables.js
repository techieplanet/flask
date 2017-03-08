/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var recentDcEventTable = null;

function setUpUsersByLocationTableBody(res)
{
    
    if($("#usersByLocationTable").length)
    {
        var data = JSON.parse(res);
        //console.log(JSON.stringify(data));
        $("#usersByLocationTableBody").html("");
        
        console.log("Before Adding Date");
        var dateString = data['timeline'];
        console.log("After Adding Date");
        
        $("#usersByLocationTable .overview-table").text(dateString);
        
        for(var i in data)
        {
            if(i === 'timeline')
                continue;
            
            var row = '<tr>';
            row += '<td>' + data[i].location_name + '</td>';
            row += '<td>' + data[i].users_count + '</td>';
            row += '<td>' + data[i].logins + '</td>';
            row += '<td>'+ data[i].active_users + '</td>';
            row += '<td>' + data[i].total_sessions +  '</td>';
            row += '</tr>';
            
            $("#usersByLocationTableBody").append(row);
        }
        
        $("#usersByLocationTable").dataTable();
    }
}

function getUsersByLocation()
{
     
    var formdata = $("#dashboard_form2").serialize();
    genericAjax('../analyticsquery/getDetailsByLocation',formdata,setUpUsersByLocationTableBody);
}


function setUpRecentDcEventsTable(jsonRes)
{
    var data = JSON.parse(jsonRes);
    
    if(recentDcEventTable != null){
        recentDcEventTable.destroy();
    }
    
    $("#recentDcEventBody").html("");
    
    
    //console.log(JSON.stringify(data));
    
    for(var k in data)
    {
        var tr = '<tr>';
        tr += '<td>' + data[k].fullname + '</td>';
        tr += '<td>' + data[k].location + '</td>';
        tr += '<td>' + data[k].activity + '</td>';
        tr += '<td>' + data[k].details + '</td>';
        tr += '<td>' + data[k].time + '</td>';
        tr += '</tr>';
        
        $("#recentDcEventBody").append(tr);
    }
    
    
    recentDcEventTable = $("#recentDcEvent").DataTable({
        "order": [[ 4, "desc" ]]
    });
    
}




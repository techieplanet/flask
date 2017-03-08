/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function ctest() {
    var colors = ['blue','green','yellow','orange','red'];
    var names = ['First', 'Second','Third','Fourth','Fifth'];
 
    $('#logins_barchart').highcharts({

        chart: {
            type: 'bar',
        },
        legend: {
            enabled: true,
	    	layout: 'vertical',
    		align: 'right',
			verticalAlign: 'middle',
            labelFormatter: function() {
				return this.name + " - <span class='total'>"+this.y+"</span>"
            }
        },
        title: {
            text: 'Simple Bar Graph'
        },
        xAxis: {
            categories: ['First', 'Second', 'Third', 'Fourth', , 'Fifth'],
            allowDecimals: false
        },
        yAxis: {
            allowDecimals: false
        },
        plotOptions: {
            series: {
                events: {
                    legendItemClick: function (x) {
                        var i = this.index  - 1;
                        var series = this.chart.series[0];
                        var point = series.points[i];   

                        if(point.oldY == undefined)
                           point.oldY = point.y;

                        point.update({y: point.y != null ? null : point.oldY});
                    }
                }
            }
        },
        legend: {
            labelFormatter: function(){
                return names[this.index-1];
            }
        },
        series: [
            {
                pointWidth:20,
                color: colors[0],
                showInLegend:false,
                data: [
                    {y: 6, name: 'First', color: colors[0]},
                    {y: 7, name: 'Second', color: colors[1]},
                    {y: 9, name: 'Third', color: colors[2]},
                    {y: 1, name: 'Fourth', color: colors[3]},
                    {y: 1, name: 'Fifth', color: colors[4]}
                ]
            },
            {color: 'blue'},
            {color: 'green'},
            {color: 'yellow'},
            {color: 'orange'},
            {color: 'red'}
            
        ],

    });

}

function setUpDailyUserLoginsByLocatioinChart(loginsDataJson){
        var loginsAndDateObject = JSON.parse(loginsDataJson);
        var locationLoginsObj = loginsAndDateObject.data;
        var month = loginsAndDateObject.month;
        var year = loginsAndDateObject.year;
        
        //console.log('this is the parsed response: ' + loginsAndDateObject.data); return;
        
        var locations = new Array(); var locationLogins = new Array();
        for (var i=0; i<locationLoginsObj.length; i++){
           locations.push(locationLoginsObj[i].location_name);
           locationLogins.push(locationLoginsObj[i].logins);
         }

        //locations =  locations.substring(0, locations.length - 1);
        //locationLogins =  locationLogins.substring(0, locationLogins.length - 1);            

        console.log(locations); console.log(locationLogins); 
        //return;
        
            $('#logins_barchart').highcharts({
                chart: {
                    type: 'column',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Total daily user logins for ' + month + ', ' + year,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
                    
                },
                legend: {
                    enabled: false
                },
                xAxis: {
                    categories: locations
                },
                yAxis: {
                    title: {
                        text: "User logins"
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true
                    },
                    column: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                credits: {enabled: false},
                series: [{
                    data: locationLogins
                }]
            });
    }
    


function setUpUserLoginsLastMonthsByLocationChart(jsonRes)
{
  //Process and Prepare LineChart Data
  
  var chartData = JSON.parse(jsonRes);
  var categories = chartData.categories;
  var series = chartData.series;
  
 //Render Line Chart
 
 $('#dashboard-linechart').highcharts({
        
        chart: {
                    type: 'line',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Sum of daily user log-ins for all geographies for each of the last 12 months',
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    layout: 'vertical',
                  align: 'right',
                  verticalAlign: 'middle',
                  borderWidth: 0
                            },
        
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'User logins'
                    }
                },
                series: series,
                credits: {enabled: false}

    });

}
    
    
    
    
    
    function setUpDailySessionsByChartSubButton(chartsData)
    {
        
        genericAjax('../analyticsquery/getDailySessionsLastMonthsByCharts',{},setUpDailySessionsLastMonthsByCharts);
        
        var categories = new Array();
        var values = new Array();
        console.log(chartsData);
        var chartsData = JSON.parse(chartsData);
        
        var year = '';
        var month = '';
        
        
        for(var d in chartsData)
        {
            if(d == 'year'){
                year = chartsData[d];
                continue
            }
            if(d == 'month'){
                month = chartsData[d];
                continue
            }
            
            categories.push(d);
            values.push(chartsData[d]);
        }
        
        
        
        console.log(categories);
        console.log(values);
       
        
        $('#dailySessions_barchart').highcharts({
                chart: {
                    type: 'column',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Daily sessions by chart sub-pages for ' + month + ", " + year ,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    enabled: false
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: "Sessions"
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true
                    },
                    column: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                credits: {enabled: false},
                series: [{
                    data: values
                }]
            });
    }
    
    
    
    
    
    
    function setUpDailySessionsByQueries(queriesData)
    {
        
        
        var categories = new Array();
        var values = new Array();
        var queriesData = JSON.parse(queriesData);
        
        var year = '';
        var month = '';
        
        for(var d in queriesData)
        {
            if(d == "year"){
                year = queriesData[d];
                continue;
            }
            
            if(d == "month"){
                month = queriesData[d];
                continue;
            }
            categories.push(d);
            values.push(queriesData[d]);
        }
        
        console.log(categories);
        console.log(values);
       
        
        $('#dailySessionsQueries_barchart').highcharts({
                chart: {
                    type: 'column',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Daily sessions by query for the month of ' + month + ", " + year,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    enabled: false
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: "Sessions"
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true
                    },
                    column: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                credits: {enabled: false},
                series: [{
                    data: values
                }]
            });
    }





function setUpDailySessionsByDC(dcData)
    {
        
        
        var categories = new Array();
        var values = new Array();
        var dcData = JSON.parse(dcData);
        
        var year = '';
        var month = '';
        var day = '';
        
        for(var d in dcData)
        {
            if(d == "year"){
                year = dcData[d];
                continue;
            }
            if(d == "month"){
                month = dcData[d];
                continue;
            }
            if(d == "day"){
                day = dcData[d];
                continue;
            }
            categories.push(d);
            values.push(dcData[d]);
        }
        
        console.log(categories);
        console.log(values);
       
        var from = "01 " + month + " " + year;
        var to = day + " " + month + " " + year;
        
        $('#dailySessionsDC_barchart').highcharts({
                chart: {
                    type: 'column',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Total sessions on data collection all geographies most recent month of ' + month + ", " + year,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    enabled: false
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'sessions'
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true
                    },
                    column: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                tooltip: {
                        formatter: function() {
                            return '<b>' + this.x + '</b>  <b>' + this.y + '</b>';
                        }
                  },
                credits: {enabled: false},
                series: [{
                    data: values
                }]
            });
    }





function setUpDailySessionsLastMonthsByCharts(jsonRes)
{
  //Process and Prepare LineChart Data
  var categories = new Array();
  var series = new Array();
  

  var jsonData = JSON.parse(jsonRes);


  
  for(var k1 in jsonData)
  {
      console.log("testing " + k1);
      for(var k2 in jsonData[k1]) 
      {
         if(k2 == "timeline")
         {
             categories.push(jsonData[k1][k2]);
             
         }
       
         
      }  
  } // End of LineChart Data Processing
  
  for(var k1 in jsonData)
  {
      for(var k2 in jsonData[k1])
      {
          if(k2 != "timeline")
          {
              var seriesObj = {};
              var dataArr = new Array();
              var count = jsonData.length;
              for(var i=0; i<count;i++)
              {
                  for(var k3 in jsonData[i])
                  {
                      if(k2 == k3)
                      {
                         dataArr.push(jsonData[i][k3])   
                      }
                  }
              }
              
              seriesObj.data = dataArr;
              seriesObj.name = k2;
              series.push(seriesObj);
          }
      }
      
      break;
  }
  
  var len = categories.length;
  
  
  var fullMonth = {
      Jan:"January",Feb:"February", Mar : "March", May : "May", Jun : "June", 
      Jul : "July", Aug : "August", Sep : "September", Oct : "October", Nov : "November", Dec : "Decembar"
  };
  
  var start = categories[0].split(" ");
  var startMonth = start[0];
  var startYear = start[1];
  
  var end = categories[len-1].split(" ");
  var endMonth = end[0];
  var endYear = end[1];
  
  for(var k in fullMonth){
      if(startMonth == k)
          startMonth = fullMonth[k];
      if(endMonth == k)
          endMonth = fullMonth[k]
  }
  
  
  
 var startDate = startMonth + " " + startYear;
 var endDate = endMonth + " " + endYear;
 
 
 $('#moduleschart-linechart').highcharts({
        
        chart: {
                    type: 'line',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Total daily sessions for all geographies, ' + startDate + " to " + endDate,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    
                  
                  
                            },
        
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Sessions'
                    }
                },
                series: series,
                credits: {enabled: false}

    });

}




function setUpDailySessionsLastMonthsByQueries(jsonRes)
{
  //Process and Prepare LineChart Data
  var categories = new Array();
  var series = new Array();
  

  var jsonData = JSON.parse(jsonRes);


  
  for(var k1 in jsonData)
  {
      console.log("testing " + k1);
      for(var k2 in jsonData[k1]) 
      {
         if(k2 == "timeline")
         {
             categories.push(jsonData[k1][k2]);
             
         }
       
         
      }  
  } // End of LineChart Data Processing
  
  for(var k1 in jsonData)
  {
      for(var k2 in jsonData[k1])
      {
          if(k2 != "timeline")
          {
              var seriesObj = {};
              var dataArr = new Array();
              var count = jsonData.length;
              for(var i=0; i<count;i++)
              {
                  for(var k3 in jsonData[i])
                  {
                      if(k2 == k3)
                      {
                         dataArr.push(jsonData[i][k3])   
                      }
                  }
              }
              
              seriesObj.data = dataArr;
              seriesObj.name = k2;
              series.push(seriesObj);
          }
      }
      
      break;
  }
  
  var startDate = categories[0];
  var endDate = categories[categories.length - 1];
 //console.log(JSON.stringify(series))
 //Render Line Chart
 
 $('#modulesquery-linechart').highcharts({
        
        chart: {
                    type: 'line',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Daily sessions by query, ' + startDate + " to " + endDate,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    
                            },
        
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Sessions'
                    }
                },
                series: series,
                credits: {enabled: false}

    });

}





function setUpDailySessionsLastMonthsByDc(jsonRes)
{
  
  
  //Process and Prepare LineChart Data
  var categories = new Array();
  var series = new Array();
  

  var jsonData = JSON.parse(jsonRes);


  
  for(var k1 in jsonData)
  {
      console.log("testing " + k1);
      for(var k2 in jsonData[k1]) 
      {
         if(k2 == "timeline")
         {
             categories.push(jsonData[k1][k2]);
             
         }
       
         
      }  
  } // End of LineChart Data Processing
  
  for(var k1 in jsonData)
  {
      for(var k2 in jsonData[k1])
      {
          if(k2 != "timeline")
          {
              var seriesObj = {};
              var dataArr = new Array();
              var count = jsonData.length;
              for(var i=0; i<count;i++)
              {
                  for(var k3 in jsonData[i])
                  {
                      if(k2 == k3)
                      {
                         dataArr.push(jsonData[i][k3])   
                      }
                  }
              }
              
              seriesObj.data = dataArr;
              seriesObj.name = k2;
              series.push(seriesObj);
          }
      }
      
      break;
  }
  
 //console.log(JSON.stringify(series))
 //Render Line Chart
 
 var startDate = categories[0];
 var endDate = categories[categories.length - 1];
 
 $('#modulesDC-linechart').highcharts({
        
        chart: {
                    type: 'line',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Total sessions on data collection all geographies over time, ' + startDate + " to " + endDate,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                   
                            },
        
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Sessions'
                    }
                },
                series: series,
                credits: {enabled: false}

    });

}


function setUpSumTotalUsersByGeo(jsonRes)
{
    var jsonData = JSON.parse(jsonRes);
    var categories = new Array();
    var series = new Array();
    
    var dateString = '';
    
    for(var k in jsonData)
    {
        if(k == "date")
        {
            dateString = jsonData[k];
        }
        else {
        categories.push(k);
        series.push(jsonData[k]);
        }
    }
    
    
    $('#sumTotUser_barchart').highcharts({
                chart: {
                    type: 'column',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Sum of total registered users by geography for ' + dateString,
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
//                    text: '(' + month + ', ' + year + ')',
//                    ///style: { fontSize: '16px' },
//                    x: -20 //center
                },
                legend: {
                    enabled: false
                },
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Registered users'
                    }
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true
                    },
                    column: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                credits: {enabled: false},
                series: [{
                    data: series
                }]
            });
}


function setUpSumTotalUsersLast12Months(jsonRes)
{
    var jsonData = JSON.parse(jsonRes);
    var categories = new Array();
    
    var series = new Array();
    
    var dateString = "";
    
    for(var k in jsonData)
    {
        if(k == "categories")
        {
            categories = jsonData[k];
        }
        else if(k == "date")
        {
            dateString = jsonData[k];
        }
        else
        {
        var seriesObj = {};
        seriesObj.data = jsonData[k];
        seriesObj.name = k;
        
        series.push(seriesObj);
        }
    }
    
    
    
    $('#sumTotUsers-linechart').highcharts({
        
        chart: {
                    type: 'line',
                    reflow: true,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    events: {
                        load:function(){
                            $("text:contains(Highcharts.com)").css("display","none");
                        }
                    },
                    height: 430
                },
                title: {
                    text: 'Sum of total registered users for all geographies for each of the last 12 months',
                    style: {
                        fontWeight: 'bold', fontFamily: 'Arial', fontSize: '16px'
                    },
                    x: -20 //center
                },
                subtitle: {
                    text: dateString,
                    style: { fontSize: '16px' },
                    x: -20 
                },
                legend: {
                   
                            },
        
                xAxis: {
                    categories: categories
                },
                yAxis: {
                    title: {
                        text: 'Registered users'
                    }
                },
                series: series,
                credits: {enabled: false}

    });

}



/**
 * This function is called onclick of filter button in the chart region
 * of the dashboard_form form.
 */

function getDailyUserLoginsByLocatioin()
{
    var formData = $("#dashboard_form").serialize();
    genericAjax('../analyticsQuery/getUserLoginsByLocation', formData, setUpDailyUserLoginsByLocatioinChart);
    genericAjax('../analyticsQuery/getUserLoginsLastMonthsByLocation', formData, setUpUserLoginsLastMonthsByLocationChart);
}

function getDailySessionsByChart()
{
    var formData = $("#dashboard_form").serialize();
    genericAjax('../analyticsquery/getDailySessionsByChartSubButton',formData,setUpDailySessionsByChartSubButton);
    genericAjax('../analyticsquery/getDailySessionsLastMonthsByCharts',formData,setUpDailySessionsLastMonthsByCharts);
}

function getDailySessionsByQueries()
{
    var formData = $("#dashboard_form").serialize();
    genericAjax('../analyticsquery/getDailySessionsByQueries',formData,setUpDailySessionsByQueries);
    genericAjax('../analyticsquery/getDailySessionsLastMonthsByQueries',formData,setUpDailySessionsLastMonthsByQueries);
}



function getDailySessionsByDataCollection()
{
    var formData = $("#dashboard_form").serialize();
    console.log(JSON.stringify(formData));
    genericAjax('../analyticsquery/getDailySessionsByDataCollection',formData,setUpDailySessionsByDC);
    genericAjax('../analyticsquery/getDailySessionsLastMonthsByDc',formData,setUpDailySessionsLastMonthsByDc);
    genericAjax('../analyticsquery/getRecentDcEvent',formData,setUpRecentDcEventsTable);
    //setUpRecentDcEventsTable();
}


function getSumTotalUsersByGeo()
{
    var formData = $("#dashboard_form").serialize();
    
    genericAjax('../analyticsquery/getSumTotalUsersByGeo',formData,setUpSumTotalUsersByGeo);
    genericAjax('../analyticsquery/getSumTotalUsersLast12Months',formData,setUpSumTotalUsersLast12Months);
}
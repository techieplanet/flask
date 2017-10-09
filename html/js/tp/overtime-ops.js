/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function getLastTwelveMonthsWithYears(latestDate){
    dateArray = latestDate.split(" ");
    monthNumber = monthNameToNumber(dateArray[0]); // month number from latest date
    currentYear = parseInt(dateArray[1]); //year from latest date
    
    monthNames = getLastTwelveMonths(monthNumber);
    monthNames.reverse();
    
    currentYearStartIndex = 12 - monthNumber; 
    lastYearEndIndex = currentYearStartIndex - 1;
    
    for(i=0; i<=lastYearEndIndex; i++)
        monthNames[i] += ", " + (currentYear - 1);
    
    for(i=currentYearStartIndex; i<12; i++)
        monthNames[i] += ", " + currentYear;
    
    return monthNames;
}

function getLastTwelveMonths(monthNumber){
    var count =1;
    var monthNames = new Array();
    
    while(count <= 12){
        monthNumber = monthNumber == 0 ? 12 : monthNumber % 12;
        monthNames.push(monthNumberToName(monthNumber));
        monthNumber -= 1;
        count++;
    }
    
    return monthNames;
}

function monthNameToNumber(monthName){
    switch(monthName){
        case 'January': return 1;
        case 'February': return 2;
        case 'March': return 3;
        case 'April': return 4;
        case 'May': return 5;
        case 'June': return 6;
        case 'July': return 7;
        case 'August': return 8;
        case 'September': return 9;
        case 'October': return 10;
        case 'November': return 11;
        case 'December': return 12;
    }
}

function monthNumberToName(monthNumber){
    switch(monthNumber){
        case 1:  return 'January';
        case 2:  return  'February'; 
        case 3:  return  'March';
        case 4:  return  'April';
        case 5: return  'May'; 
        case 6: return  'June'; 
        case 7: return  'July'; 
        case 8: return  'August'; 
        case 9: return  'September';
        case 10: return  'October';
        case 11: return  'November'; 
        case 12: return  'December' 
    }
}

function processOvertimeData(overtimeData){
    //declare the array in which to store data
    var dataArray = new Array();

    //get month names for x axis: keys of the moded array
    var monthNames = [];
    for(month in overtimeData)
        monthNames.push(month);

      //get the location names: keys for month. Locations for each month are same
      var firstMonth = overtimeData[monthNames[0]];
      var locationNames = [];
      for(loc in firstMonth)
          locationNames.push(loc);

      locationNames.sort();
      for(loc in locationNames){
          loopLocation = locationNames[loc];
          obj = {name: loopLocation, data: []};

          for(month in monthNames){
              loopMonth = monthNames[month];
              obj.data.push(overtimeData[loopMonth][loopLocation]['percent']);
          }

            if(loopLocation == 'National'){
                obj.marker = {enabled: true};
                obj.color = '#000000';
                obj.dashStyle = 'dot';
            }

            dataArray.push(obj);
      }

    return dataArray;
}


function processMonthNamesWithYears(selectedDatemultiple, startDate){
    var monthNamesWithYears = new Array();
        if(selectedDatemultiple.length == 0){
            monthNamesWithYears = getLastTwelveMonthsWithYears(startDate);
        } else {
            selectedDatemultiple.sort();
            for(key in selectedDatemultiple){
                monthArray = selectedDatemultiple[key].split("-");
                monthNumber = parseInt(monthArray[1]);
                console.log('monthNumber: ' + monthNumber);
                monthAndYear = monthNumberToName(monthNumber) + ", " + parseInt(monthArray[0]);
                monthNamesWithYears.push(monthAndYear);
            }
        }
        
    return monthNamesWithYears;
}
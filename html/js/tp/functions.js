/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


   $('.infowrap').click(function(){
       console.log("inside click");
//       $(this).siblings(".infodiv").slideToggle( "slow", function() {
//            // Animation complete.
//       });

        $(this).siblings(".infodiv").animate({
            opacity: 1,
            height: "toggle",
            width: "toggle"
          }, 200, function() {
            // Animation complete.
          });
          //console.log('click function');
        return false;
    }); 

     
      //function setUpDailyUserLoginsByLocatioinChart(url, callback){
      function genericAjax(url, formData, callback){
          //URL FORM: '../analyticsQuery/getUserprofile'
          //console.log('FORM: ' + formData);
          //startLoading();
          $.ajax({
              url : url, 
              data: formData,
              type : 'post',
              success : function(res){
                  callback(res);
                  //stopLoading();
              }
          })
      }

function thousandSeparator(number){
    var valueArray = (number + "").split('').reverse();
    var value = '';
    for(var i=0; i<valueArray.length; i++)
        value = (i>0 && i%3 == 0) ? valueArray[i] + "," + value : valueArray[i] + value;
    
    return value;
}
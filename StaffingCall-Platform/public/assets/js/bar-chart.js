$(function () {

//  First Chart
   var barData = {
       labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
       datasets: [
           {
               label: lastMinuteShiftCancellationTitle,
               backgroundColor: 'rgba(66,165,245,.75)',
               pointBorderColor: "#fff",
               data: lastMinuteShiftCancellations
           }
       ]
   };
   var barOptions = {
       responsive: true,
       legend: {
           display: false
       },
       scales: {
           xAxes: [{
           barPercentage: 0.9
       }],
       yAxes: [{
           ticks: {
               beginAtZero:true,
               /*stepSize: 1*/
           }
       }]
     }
   };
//  Second Chart
   var barData2 = {
       labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
       datasets: [
           {
               label: averageTimeShiftCancellationTitle,
               backgroundColor: 'rgba(66,165,245,.75)',
               pointBorderColor: "#fff",
               data: averageTimeShiftCancellation
           }
       ]
   };
   var barOptions2 = {
       responsive: true,
       legend: {
           display: false
       },
       scales: {
           xAxes: [{
           barPercentage: 0.9
       }],
       yAxes: [{
           ticks: {
               beginAtZero:true
           }
       }]
     }
   };

   var ctx = document.getElementById("cancellationChart").getContext("2d");
   new Chart(ctx, {type: 'bar', data: barData, options:barOptions});

   var ctx2 = document.getElementById("AvgTime").getContext("2d");
   new Chart(ctx2, {type: 'bar', data: barData2, options:barOptions2});
   

});